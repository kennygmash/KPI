<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    // Show the application index page
    public function index()
    {
        // Create user
        $user = null;

        return view('auth.register', compact('user'));
    }


}
