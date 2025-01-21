<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    function login() {
        return view('pages.auth.login');
    }
    function auth() {
        return redirect('login');
    }
    function logout() {
        return redirect('login');
    }
}
