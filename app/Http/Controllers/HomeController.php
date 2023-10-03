<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FileUpload;
use App\GeneralFileUpload;
use App\Http\Requests\CreateWorkPlanRequest;
use App\Http\Requests\UpdateWorkPlanRequest;
use App\User;
use App\WorkPlan;
use App\Project;
use App\Activity;
use App\Department;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
//use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $supervisees = $request->user()->load(
                    'supervisees:id,name,supervisor_id,job_group_id,designation_id',
                    'supervisees.jobGroup:id,name',
                    'supervisees.designation:id,name'
                );
        return view('home',compact('supervisees'));
    }

    public function landingView(){
        return view('staff.landing');
    }

    public function visionView(){
            return view('staff.vision');
    }

    public function trainingView(){
        $projects=Project::get();
        $activities=Activity::get();
        $departments=Department::get();
     return view('staff.training-needs',compact('projects','activities','departments'));
    }
    public function missionView(){
            return view('staff.mission');
    }

    public function goalView(){
            return view('staff.goal');
    }
    public function projectView(){
           return view('projects.create');
    }

    public function changePasswordView()
    {
        //
        return \view('auth.changePassword');
    }

    public function updateUserPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'payroll_number' => ['required', 'string'],
            'password' => ['required', 'confirmed']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Sorry. The entered passwords do not match.');
        }

        // Extract the request data
        $staffNumber = $request->input('payroll_number');

        // Find the user
        $user = User::query()->where('payroll_number', $staffNumber);

        // Check if they exist
        if (!$user->first('id')) {
            return redirect()->back()->withInput($request->all())->with('error', 'Sorry. No employee was found.');
        }

        $user->update([
            'password' => bcrypt($request->input('password'))
        ]);

        return redirect()->back()->with('success', 'User password updated successfully.');
    }






}
