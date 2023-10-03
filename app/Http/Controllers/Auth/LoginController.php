<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/staff/landing';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Attempt to login the user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Extract the request data
        $payrollNumber = $request->input('payroll_number');

        $userModel = User::query();

        // Check if the payroll number exists
        $existingUser = $userModel->whereIn('payroll_number', [$payrollNumber])->first([
            'id', 'password'
        ]);

        if (!$existingUser) {
            return redirect()->back()->with('error', 'We colidn\'t find a user with that payroll number.')
                ->withInput($request->all());
        }

        // Check if the user has a password
        if (is_null($existingUser->password)) {
            return redirect()->route('register')->with('info', 'Please set an account password for your account.')
                ->withInput($request->all());
        }

        $credentials = [
            'payroll_number' => $payrollNumber,
            'password' => $request->input('password')
        ];

        // Check the password
        if (!auth()->attempt($credentials, true)) {
            return redirect()->back()->with('error', 'Please enter a valid payroll number or password.')
                ->withInput($request->all());
        }

        // Login the user
        auth()->loginUsingId($existingUser->id);

        return auth()->user()->is_supervisor ?
            redirect()->route('staff.landing') :
            redirect()->route('staff.landing');
    }

    public function username()
    {
        return 'payroll_number';
    }
}
