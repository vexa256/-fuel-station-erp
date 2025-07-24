<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'employee_number',
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    //  Add name accessor for Breeze compatibility
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Override Eloquent methods to use Query Builder
    public static function create(array $attributes = [])
    {
        // Parse name if provided
        if (isset($attributes['name']) && !isset($attributes['first_name'])) {
            $nameParts = explode(' ', trim($attributes['name']), 2);
            $attributes['first_name'] = $nameParts[0];
            $attributes['last_name'] = $nameParts[1] ?? 'User';
        }

        // Add required fields
        $userData = [
            'employee_number' => self::generateEmployeeNumber(),
            'username' => self::generateUsername($attributes['first_name'] ?? 'user', $attributes['last_name'] ?? 'name'),
            'email' => $attributes['email'],
            'password' => $attributes['password'], // Already hashed by cast
            'first_name' => $attributes['first_name'] ?? 'User',
            'last_name' => $attributes['last_name'] ?? 'Name',
            'role' => $attributes['role'] ?? 'STATION_MANAGER',
            'department' => 'Operations',
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
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 1
        ];

        $userId = DB::table('users')->insertGetId($userData);

        // Return Eloquent model instance
        return static::find($userId);
    }

    private static function generateEmployeeNumber()
    {
        return DB::transaction(function () {
            $maxNumber = DB::table('users')
                ->lockForUpdate()
                ->whereRaw('employee_number REGEXP "^EMP[0-9]+$"')
                ->selectRaw('MAX(CAST(SUBSTRING(employee_number, 4) AS UNSIGNED)) as max_num')
                ->value('max_num');

            $nextNumber = ($maxNumber ?? 0) + 1;

            return 'EMP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });
    }

    private static function generateUsername($firstName, $lastName)
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

    // FUEL_ERP specific methods
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function getStations()
    {
        return DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->where('user_stations.user_id', $this->id)
            ->where('user_stations.is_active', 1)
            ->select('stations.*', 'user_stations.assigned_role')
            ->get();
    }
}
