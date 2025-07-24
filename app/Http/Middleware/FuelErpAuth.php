<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelErpAuth
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        // Verify user exists and is active
        $user = DB::table('users')
            ->where('id', $userId)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            session()->flush();
            return redirect()->route('login')->withErrors(['error' => 'Session invalid']);
        }

        // Check account lockout
        if ($user->account_locked_until && now() < $user->account_locked_until) {
            session()->flush();
            return redirect()->route('login')->withErrors(['error' => 'Account is locked']);
        }

        // Check role permissions
        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access');
        }

        // Update session activity
        DB::table('sessions')->where('id', session()->getId())->update([
            'last_activity' => now()->timestamp
        ]);

        //  Convert stdClass to array before merging
        $request->merge([
            'auth_user' => (array) $user,  // Convert to array
            'auth_user_id' => $user->id,
            'auth_user_role' => $user->role
        ]);

        return $next($request);
    }
}
