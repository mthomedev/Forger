<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return User::all()->map(function ($user) {
            return ['name' => $user->name];
        });
    }
}
