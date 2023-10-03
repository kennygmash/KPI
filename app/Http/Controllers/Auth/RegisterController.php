<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use URL;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By defalit this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'payroll_number' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function showRegistrationForm()
    {
        $user = null;

        return view('auth.register', compact('user'));
    }

    /**
     * Create a new user
     * @param Request $request
     * @return RedirectResponse
     */
    public function register(Request $request)
    {
        // Extract the request data
        $payrollNumber = $request->input('payroll_number');

        $userModel = User::query();

        // Check if the payroll number exists
        $existingUser = $userModel->where('payroll_number', $payrollNumber);

        if (!$existingUser->first(['id'])) {
            return redirect()->back()->with('error', 'We colidn\'t find a user with that payroll number.')
                ->withInput($request->all());
        }

        $user = $existingUser->with(
            'jobGroup:id,name', 'designation:id,name', 'department:id,name', 'campus:id,name', 'supervisor:id,name'
        )->first([
            'id', 'name', 'password', 'payroll_number', 'job_group_id', 'designation_id', 'department_id', 'campus_id'
        ]);

        // Check if the user has a password
        if (!is_null($user->password)) {
            return redirect()->route('login', [
                'payrollNumber' => $payrollNumber
            ])->with('info', 'You had already registered. Please login.');
        }

        return redirect()->to(URL::signedRoute('create-password', [
            'payroll_number' => $payrollNumber,
        ]));
    }

    /**
     * Show the view for creating the user password
     * @param Request $request
     * @return Factory|View
     */
    public function createPasswordForm(Request $request)
    {
        // Find the user using the payroll number
        $user = User::with(
            'jobGroup:id,name', 'designation:id,name', 'department:id,name', 'campus:id,name', 'supervisor:id,name'
        )->where('payroll_number', $request->query('payroll_number'))
            ->firstOrFail([
                'id', 'name', 'payroll_number', 'job_group_id', 'designation_id', 'department_id', 'campus_id', 'supervisor_id'
            ]);

        return view('auth.create-password', compact('user'));
    }

    /**
     * Create a password and login the user.
     * @param Request $request
     * @return RedirectResponse
     */
    public function createPassword(Request $request)
    {
        // Validate the request
        $this->validator($request->all())->validate();

        // Extract the request data
        $payrollNumber = $request->input('payroll_number');

        // Update the user password
        $user = User::query()->where('payroll_number', $payrollNumber)->firstOrFail([
            'id', 'password'
        ]);

        $user->update([
            'password' => bcrypt($request->input('password'))
        ]);

        // Login the user
        auth()->loginUsingId($user->id);

        return auth()->user()->is_supervisor ?
            redirect()->route('supervisor.home') :
            redirect()->route('home');
    }
}
