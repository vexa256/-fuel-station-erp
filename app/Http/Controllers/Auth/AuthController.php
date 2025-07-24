<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  //  ADD THIS LINE

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //  Now you can use Auth::attempt()!
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Add our custom session data
            session([
                'user_role' => Auth::user()->role,
                'user_name' => Auth::user()->name
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    // Registration Form
    public function showRegister()
    {

        return view('auth.register');
    }

    // Handle Registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:STATION_MANAGER,DELIVERY_SUPERVISOR'
        ]);

        // Parse name
        $nameParts = explode(' ', trim($request->name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'User';

        // Create user in YOUR schema format
        $userData = [
            'employee_number' => $this->generateEmployeeNumber(),
            'username' => $this->generateUsername($firstName, $lastName),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $request->role,
            'department' => $this->getDepartmentByRole($request->role),
            'hire_date' => now()->toDateString(),
            'security_clearance_level' => 'BASIC',
            'can_approve_variances' => 0,
            'can_approve_purchases' => 0,
            'can_modify_prices' => 0,
            'can_access_financial_data' => 0,
            'can_export_data' => 0,
            'max_approval_amount' => 0.00,
            'failed_login_attempts' => 0,
            'is_active' => 1,
            'email_verified_at' => now(), // Auto-verify for internal users
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 1 // System user
        ];

        $userId = DB::table('users')->insertGetId($userData);

        // Audit log
        $this->logAuditAction($userId, 'CREATE', 'USER_REGISTRATION', $userData, $request);

        // Auto-login after registration
        $userData['id'] = $userId;
        $this->createUserSession((object) $userData, $request);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    // // Logout
    // public function logout(Request $request)
    // {
    //     $userId = session('user_id');

    //     if ($userId) {
    //         $this->logAuditAction($userId, 'LOGOUT', 'USER_LOGOUT', [], $request);
    //     }

    //     session()->flush();
    //     session()->regenerateToken();

    //     return redirect()->route('login')->with('success', 'Logged out successfully');
    // }

    // Password Reset Request Form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Handle Password Reset Request
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('is_active', 1)
            ->first();

        if ($user) {
            $token = Str::random(64);

            // Store reset token
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Send email (implement your email logic)
            $this->sendPasswordResetEmail($request->email, $token);

            // Audit log
            $this->logAuditAction($user->id, 'READ', 'PASSWORD_RESET_REQUEST', [], $request);
        }

        return back()->with('status', 'Password reset link sent to your email (if account exists)');
    }

    // Password Reset Form
    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    // Handle Password Reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>', now()->subHours(1)) // 1 hour expiry
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token']);
        }

        // Update password
        $updated = DB::table('users')
            ->where('email', $request->email)
            ->where('is_active', 1)
            ->update([
                'password' => Hash::make($request->password),
                'failed_login_attempts' => 0,
                'account_locked_until' => null,
                'updated_at' => now()
            ]);

        if ($updated) {
            // Delete reset token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Audit log
            $user = DB::table('users')->where('email', $request->email)->first();
            $this->logAuditAction($user->id, 'UPDATE', 'PASSWORD_RESET', [], $request);

            return redirect()->route('login')->with('success', 'Password reset successfully');
        }

        return back()->withErrors(['email' => 'Unable to reset password']);
    }

    // Private Helper Methods
    private function handleSuccessfulLogin($user, Request $request)
    {
        // Reset failed attempts
        DB::table('users')->where('id', $user->id)->update([
            'failed_login_attempts' => 0,
            'account_locked_until' => null,
            'last_login_at' => now(),
            'updated_at' => now()
        ]);

        // Create session
        $this->createUserSession($user, $request);

        // Audit log
        $this->logAuditAction($user->id, 'LOGIN', 'SUCCESSFUL_LOGIN', [], $request);
    }

    private function handleFailedLogin($userId, Request $request)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        $failedAttempts = $user->failed_login_attempts + 1;

        $updateData = [
            'failed_login_attempts' => $failedAttempts,
            'updated_at' => now()
        ];

        // Lock account after 3 failed attempts
        if ($failedAttempts >= 3) {
            $updateData['account_locked_until'] = now()->addMinutes(15);
        }

        DB::table('users')->where('id', $userId)->update($updateData);

        $this->logFailedLogin($user->email, 'WRONG_PASSWORD', $request);
    }

    private function createUserSession($user, Request $request)
    {
        session([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_email' => $user->email,
            'security_clearance' => $user->security_clearance_level
        ]);

        session()->regenerate();

        // Create session record
        DB::table('sessions')->updateOrInsert(
            ['id' => session()->getId()],
            [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => base64_encode(serialize(session()->all())),
                'last_activity' => now()->timestamp,
                'created_at' => now()
            ]
        );
    }

    private function logAuditAction($userId, $actionType, $actionCategory, $data, Request $request)
    {
        $dataJson = json_encode($data);
        $timestamp = now();

        // Create comprehensive hash data (this is what was missing)
        $hashData = json_encode([
            'user_id' => $userId,
            'action_type' => $actionType,
            'table_name' => 'users',
            'record_id' => $userId,
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => $timestamp->toISOString(),
            'session_id' => session()->getId()
        ]);

        DB::table('audit_logs')->insert([
            'user_id' => $userId,
            'session_id' => session()->getId(),
            'action_type' => $actionType,
            'action_category' => 'SECURITY',
            'table_name' => 'users',
            'record_id' => $userId,
            'new_value_text' => $dataJson,
            'change_reason' => $actionCategory,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => $timestamp,
            'hash_data' => $hashData, //  This was missing!
            'hash_current' => hash('sha256', $userId . $actionType . $dataJson . $timestamp->timestamp),
            'hash_algorithm' => 'SHA256', // Optional but good practice
            'risk_level' => $this->getRiskLevel($actionCategory),
            'compliance_category' => 'SECURITY',
            'system_generated' => 0,
            'batch_operation' => 0,
            'error_occurred' => 0
        ]);
    }

    // private function logAuditAction($userId, $actionType, $actionCategory, $data, Request $request)
    // {
    //     DB::table('audit_logs')->insert([
    //         'user_id' => $userId,
    //         'session_id' => session()->getId(),
    //         'action_type' => $actionType,
    //         'action_category' => 'SECURITY',
    //         'table_name' => 'users',
    //         'record_id' => $userId,
    //         'new_value_text' => json_encode($data),
    //         'change_reason' => $actionCategory,
    //         'ip_address' => $request->ip(),
    //         'user_agent' => $request->userAgent(),
    //         'timestamp' => now(),
    //         'hash_current' => hash('sha256', $userId . $actionType . json_encode($data) . time()),
    //         'risk_level' => $this->getRiskLevel($actionCategory),
    //         'compliance_category' => 'SECURITY'
    //     ]);
    // }

    // private function logFailedLogin($email, $reason, Request $request)
    // {
    //     DB::table('audit_logs')->insert([
    //         'action_type' => 'LOGIN',
    //         'action_category' => 'SECURITY',
    //         'table_name' => 'users',
    //         'old_value_text' => $email,
    //         'change_reason' => $reason,
    //         'ip_address' => $request->ip(),
    //         'user_agent' => $request->userAgent(),
    //         'timestamp' => now(),
    //         'hash_current' => hash('sha256', $email . $reason . $request->ip() . time()),
    //         'risk_level' => 'HIGH',
    //         'compliance_category' => 'SECURITY'
    //     ]);
    // }

    private function logFailedLogin($email, $reason, Request $request)
    {
        $timestamp = now();

        $hashData = json_encode([
            'action_type' => 'LOGIN',
            'email' => $email,
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => $timestamp->toISOString()
        ]);

        DB::table('audit_logs')->insert([
            'action_type' => 'LOGIN',
            'action_category' => 'SECURITY',
            'table_name' => 'users',
            'old_value_text' => $email,
            'change_reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => $timestamp,
            'hash_data' => $hashData, //  Added this
            'hash_current' => hash('sha256', $email . $reason . $request->ip() . $timestamp->timestamp),
            'hash_algorithm' => 'SHA256',
            'risk_level' => 'HIGH',
            'compliance_category' => 'SECURITY',
            'system_generated' => 1,
            'batch_operation' => 0,
            'error_occurred' => 0
        ]);
    }

    private function generateEmployeeNumber()
    {
        // Get the highest existing employee number
        $lastEmployee = DB::table('users')
            ->where('employee_number', 'LIKE', 'EMP%')
            ->orderByRaw('CAST(SUBSTRING(employee_number, 4) AS UNSIGNED) DESC')
            ->value('employee_number');

        if ($lastEmployee) {
            // Extract number from EMP003 -> 003 -> 3
            $lastNumber = (int) substr($lastEmployee, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            // No employees yet, start with 1
            $nextNumber = 1;
        }

        return 'EMP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function generateUsername($firstName, $lastName)
    {
        $base = strtolower($firstName . '.' . $lastName);
        $username = $base;
        $counter = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function getDepartmentByRole($role)
    {
        return match ($role) {
            'STATION_MANAGER' => 'Operations',
            'DELIVERY_SUPERVISOR' => 'Logistics',
            'AUDITOR' => 'Finance',
            'CEO' => 'Executive',
            default => 'General'
        };
    }

    private function getRiskLevel($actionCategory)
    {
        return match ($actionCategory) {
            'USER_REGISTRATION', 'PASSWORD_RESET' => 'MEDIUM',
            'SUCCESSFUL_LOGIN' => 'LOW',
            'FAILED_LOGIN', 'ACCOUNT_LOCKED' => 'HIGH',
            default => 'MEDIUM'
        };
    }

    private function sendPasswordResetEmail($email, $token)
    {
        // Implement your email sending logic here
        // For now, just log it
        \Log::info("Password reset token for {$email}: {$token}");
    }
}
