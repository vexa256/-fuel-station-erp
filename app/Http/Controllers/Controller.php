<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getAuthUser()
    {
        $userId = session('user_id');
        return $userId ? DB::table('users')->where('id', $userId)->first() : null;
    }

    protected function hasRole($role)
    {
        return session('user_role') === $role;
    }

    protected function hasAnyRole(array $roles)
    {
        return in_array(session('user_role'), $roles);
    }
}