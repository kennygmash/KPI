<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class  WorkPlanController extends Controller
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
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user();


        $user->load('workPlans', 'workPlans.latestUpdate', 'workPlans.fileUploads','workPlans.supervisee');

        $auto_works = WorkPlan::with('fileUploads')->where('supervisee_id', auth()->id())
            ->paginate(30);


        // Get their departmental files
        $departmentalFiles = FileUpload::query()
            ->where('uploadable_id', $user->supervisor_id)
            ->where('is_departmental', true)
            ->orderByDesc('created_at')
            ->get(['public_url', 'filename', 'created_at']);


        return view('staff.home', compact('user', 'departmentalFiles', 'auto_works'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create(Request $request)
    {
        $supervisees = $request->user()->load(
            'supervisees:id,name,supervisor_id,job_group_id,designation_id',
            'supervisees.jobGroup:id,name',
            'supervisees.designation:id,name'
        );

        $projects=Project::get();
        $activities=Activity::get();
        $departments=Department::get();
        return \view('workplans.create',compact('supervisees','projects','activities','departments'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param CreateWorkPlanRequest $request
     * @return RedirectResponse
     */
    public function store(CreateWorkPlanRequest $request)
    {
        try {
            // Create a new work plan
            WorkPlan::query()->create([
                'activity' => $request->input('activity'),
                'expected_output' => $request->input('expected_output'),
                'performance_indicator' => $request->input('performance_indicator'),
                'resources_required' => $request->input('resources_required'),
                'due_date' => $request->input('due_date'),
                'time_frame' => $request->input('time_frame'),
                'user_id' => auth()->id(),
                'supervisee_id' => $request['supervisee'],
                'key_result' => $request->input('key_result'),
                'strategic_objective' => $request->input('strategic_objective'),
                'other_objectives' => $request->input('other_objectives'),
                'assumptions' => $request->input('assumptions'),
                'activities' => $request->input('activities'),
                'projects' => $request->input('projects'),
                'department' => $request->input('department'),
            ]);
        } catch (Exception $exception) {
            return  $exception;
            //return redirect()->back()->with('error', 'Sorry something went wrong. PLease try again.')
               // ->withInput($request->all());
        }
;
        return redirect()->back()->with('success', 'The work plan was successfully created.');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return Factory|RedirectResponse|View
     */
    public function show(Request $request, int $id)
    {
        // Find the user using the id, select their work plans
        $user = User::with('workPlans', 'workPlans.updates','workPlans.supervisee')->findOrFail($id);

        $auto_works = WorkPlan::with('fileUploads')->where('supervisee_id', auth()->id())
            ->paginate(30);


        // Set the is viewing action
        $request->query->set('is_viewing', true);

        // Check if the auth user is the selected user supervisor
        if ($request->user()->id != $user->supervisor_id) {
            return redirect()->back()->with('error', 'The selected action is not allowed.');
        }

        return view('staff.home', compact('user', 'auto_works'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param WorkPlan $workPlan
     * @return Factory|RedirectResponse|View
     */
    public function edit(Request $request, WorkPlan $workPlan)
    {
        // Keep track of commenting feature
        $canComment = false;

        $supervisees = $request->user()->load(
            'supervisees:id,name,supervisor_id,job_group_id,designation_id',
            'supervisees.jobGroup:id,name',
            'supervisees.designation:id,name'
        );

        $auto_works = WorkPlan::with('fileUploads')->where('supervisee_id', auth()->id())
            ->paginate(30);
        // Check if the user can updates
        if ($request->query->has('_can_comment')) {
            $canComment = true;

            return view('workplans.edit', compact('workPlan', 'canComment','supervisees', 'auto_works'));
        }

        // Check if the work plan belongs to the current auth user
        //if ($request->user()->id != $workPlan->user_id ) {
            //return redirect()->back()->with('error', 'You can only view your work plans.');
        //}

        return view('workplans.edit', compact('workPlan', 'canComment','supervisees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWorkPlanRequest $request
     * @param int $id
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function update(UpdateWorkPlanRequest $request, int $id)
    {
        // Find the work plan by id and update the progress
        $workPlan = WorkPlan::query()->find($id);

        $auto_works = WorkPlan::with('fileUploads')->where('supervisee_id', auth()->id())
            ->paginate(30);

        // Get the auth user
        $user = $request->user();

        // Check if the user is authorized to comment
        if (!$request->get('_can_comment')) {
            try {

                $workPlan->update([
                    'progress' => (bool)$request->input('progress'),
                ]);

                // Upload any files the user had uploaded
                DataController::filesUploader($request, $user, $workPlan);

                $workPlan->updates()->create([
                    'evidence' => $request->input('evidence'),
                    'work_plan_id' => $id,
                    'user_id' => $user->id,
                ]);
            } catch (Exception $exception) {
                return redirect()->back()->with('error', 'Sorry something went wrong. Please try again.')
                    ->withInput($request->all());
            }

            return redirect()->back()->with('success', 'The work plan was successflily updated.');
        }

        // Check if the comment is null or empty
        $supervisorComments = $request->input('supervisor_comment');

        if (is_null($supervisorComments) || strlen($supervisorComments) == 0) {
            return redirect()->back()->with('info', 'No comment was provided therefore no update was done.');
        }

        // Update the work plan
        $workPlan->update([
            'supervisor_comments' => $supervisorComments
        ]);

        return redirect()->route('work-plans.show', $workPlan->user_id)
            ->with('success', 'Comment was successflily submitted.');
    }

    /**
     * Generate the user reports. We get all the workplans for the user
     * @param Request $request
     * @return Factory|View
     */
    public function reportGenerator(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user()->load('workPlans');

        return view('staff.report', compact('user'));
    }

    /**
     * Find work plan using the search key provided
     * @param Request $request
     * @return Factory|View
     */
    public function searchWorkplan(Request $request)
    {
        // Get the request data
        $searchKey = $request->input('q');
        $user = $request->user();

        // Search the workplans data using the activity as the search key
        // Load the user work plans with each work plan latest update
        $user = $request->user()
            ->load([
                'workPlans' => function ($query) use ($searchKey, $user) {
                    $query->where('work_plans.activity', 'like', '%' . $searchKey . '%')->where('user_id', $user->id);
                }, 'workPlans.latestUpdate', 'workPlans.fileUploads'
            ]);

        return view('staff.home', compact('user'));
    }

    /**
     * Show the view for uploading the user work plan
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View
     */
    public function uploadIndividualUserFileView()
    {
        return \view('data-processors.upload-individual-workplan');
    }

    public function uploadIndividualUserFiles(Request $request)
    {
        $user = $request->user();

        $file = GeneralFileUpload::query()->create([
           'user_id' => $user->id,
        ]);

        DataController::filesUploader($request, $user, $file);

        return redirect()->back()->with('success', 'File was uploaded successflily.');
    }

    public function showUploadedFiles(Request $request)
    {
        $user = $request->user();

         $supervisesID = User::query()->where('supervisor_id', $user->id)->get()->map(function ($s) {
            return $s->id;
        });

         $filesIDs = GeneralFileUpload::query()->whereIn('user_id', $supervisesID)->get()->map(function ($s) {
             return $s->id;
         });

         $fileUploads = FileUpload::with('user')
            ->where('uploadable_type', 'App\GeneralFileUpload')
            ->whereIn('uploadable_id', $filesIDs)->get();


        return \view('workplans.view-prepared-workplans', compact('fileUploads'));
    }

    public function userFiles(Request $request){
        $user = $request->user();

        // Get their users' files
        $usersFiles = FileUpload::query()
            ->where('user_id', $user->id)
            ->get(['public_url', 'filename', 'created_at'])
            ->sortByDesc('created_at');

        return view('files.index', compact('user', 'usersFiles'));
    }
    public function departmentalFiles(Request $request){
        $user = $request->user();

        // Get their users' files
        $departmentalFiles = FileUpload::query()
            ->where('uploadable_id', $user->supervisor_id)
            ->where('is_departmental', true)
            ->orderByDesc('created_at')
            ->get(['public_url', 'filename', 'created_at']);

        return view('files.departmental', compact('user', 'departmentalFiles'));
    }
}
