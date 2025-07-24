<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class QueryBuilderUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $userData = DB::table('users')
            ->where('id', $identifier)
            ->where('is_active', 1)
            ->first();
            
        return $userData ? new User((array) $userData) : null;
    }
    
    public function retrieveByToken($identifier, $token)
    {
        $userData = DB::table('users')
            ->where('id', $identifier)
            ->where('remember_token', $token)
            ->where('is_active', 1)
            ->first();
            
        return $userData ? new User((array) $userData) : null;
    }
    
    public function updateRememberToken(Authenticatable $user, $token)
    {
        DB::table('users')
            ->where('id', $user->getAuthIdentifier())
            ->update(['remember_token' => $token]);
    }
    
    public function retrieveByCredentials(array $credentials)
    {
        $userData = DB::table('users')
            ->where('email', $credentials['email'])
            ->where('is_active', 1)
            ->first();
            
        return $userData ? new User((array) $userData) : null;
    }
    
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }
    
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // Optional: implement password rehashing if needed
        return null;
    }
}
