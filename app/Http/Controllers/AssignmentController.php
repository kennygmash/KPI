<?php

namespace App\Http\Controllers;

use App\Assigned;
use App\Assignments;
use App\Notifications\NewAssignment;
use App\Notifications\CompleteAssignment;
use App\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AssignmentController extends Controller
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

    private function assignmentTypes()
    {
        return [
            'work_plan_assignment',
            'other_assignment',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        // Get my assignments
        $assignments = Assignments::query()->where('user_id', auth()->id())
            ->paginate(30);

        $types = $this->assignmentTypes();

        return view('work-assignments.index', compact('assignments', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return
     */
    public function create()
    {
        $types = $this->assignmentTypes();

        return view('work-assignments.create', compact('types'));
    }

    public function showAssignForm(Request $request)
    {
        $supervisees = $request->user()->load(
            'supervisees:id,name,supervisor_id,job_group_id,designation_id',
            'supervisees.jobGroup:id,name',
            'supervisees.designation:id,name'
        );
        return view('assignments.assign', compact('supervisees'));
    }

    public function assignData(Request $request)
    {
//        return $request;
        $user = auth()->user();

        try {
            $assigned=Assigned::query()->create([
                'assignment' => $request['assignment'],
                'description' => $request['description'],
                'supervisee_id' => $request['supervisee'],
                'user_id' => $user->id,
                'from' => $request['from'],
                'to' => $request['to'],
                'status' => 'incomplete',
            ]);
            if ($request->hasFile('files')) {
                DataController::filesUploader($request, $user, $assigned);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Get the supervisee data
        $supervisee = User::query()->find($request['supervisee'], [
            'id', 'email',
        ]);

        // Check if the user email field has a value, if it does not do not send an email notification
        if (!is_null($supervisee->email)) {
            // Send the notification here
            Notification::send($supervisee, new NewAssignment($user));
        }

        return redirect()->back()->with('success', 'Assignment successfully added.');
    }

    public function showAssigned(Request $request)
    {
        // Get my assigned assignments
        $assignments = Assigned::with('fileUploads')->where('supervisee_id', auth()->id())
            ->paginate(30);
        return view('assignments.assigned',compact('assignments'));
    }
    public function showAssignedAll(Request $request)
    {
        // Get my assigned assignments
         $assignments = Assigned::with('fileUploads', 'supervisee')
            ->where('user_id', auth()->id())
            ->paginate(100);
        return view('assignments.assigned-supervisor',compact('assignments'));
    }

    public function showAssignedDetailed($id)
    {
        $assignment = Assigned::query()->find($id);

        return view('assignments.view-assigned', compact('assignment'));
    }

    public function reportGenerator(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user()->load('assignments');

        return view('staff.assignment-report', compact('user'));
    }

    public function reportGeneratorSupervisor(Request $request)
    {
        // Load the user work assignments with each work plan latest update
        $assignments = Assigned::with('fileUploads', 'supervisee')
            ->where('user_id', auth()->id())
            ->paginate(100);


        return view('staff.report-supervisor', compact('assignments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        Assignments::query()->create($request->all());

        return redirect()->back()->with('success', 'Assignment was created successfully.');
    }

    public function showUserAssignments(Request $request, int $userID)
    {
        // Get the auth user
        $authUser = $request->user();

        // Set the is viewing action
        !$authUser->is_supervisor && $authUser->id != $userID ?: $request->query->set('is_viewing', false);

        // Get my assignments
        $assignments = Assignments::query()->where('user_id', $userID)
            ->paginate(30);

        $types = $this->assignmentTypes();

        // Get the user
        $user = User::query()->find($userID);

        return view('work-assignments.index', compact('assignments', 'types', 'user'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return
     */
    public function edit($id)
    {
        $assignment = Assignments::query()->find($id);

        return view('work-assignments.edit', compact('assignment'));
    }
    public function editAssigned(Request $request,$id)
    {
        $supervisees = $request->user()->load(
            'supervisees:id,name,supervisor_id,job_group_id,designation_id',
            'supervisees.jobGroup:id,name',
            'supervisees.designation:id,name'
        );

        $assignment = Assigned::query()->find($id);
        //dd();

        return view('assignments.edit-assigned', compact('assignment','supervisees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return
     */
    public function update(Request $request, $id)
    {

        try {
            $assignment = Assignments::query()->find($id);

            $assignment->update([
                'to' => $request->input('to'),
                'progress' => $request->input('progress'),
                'evidence' => sprintf('%s </br> %s', $request->get('old_evidence'), $request->input('evidence')),
            ]);

            // Upload any files the user had uploaded
            DataController::filesUploader($request, $request->user(), $assignment);
        } catch (Exception $exception) {
            return redirect()->back()->with('error', 'Sorry. Something went wrong');
        }
        $user = auth()->user();

        $supervisor = User::query()->find($user->supervisor_id, [
            'id', 'email',
        ]);


        $assignment = Assignments::query()->find($id, ['progress']);

        if (!is_null($supervisor->email) && $assignment->progress == 'completed') {
            info('sending email now..');
            // Send the notification here
            Notification::send($supervisor, new CompleteAssignment($user));
        }

        return redirect()->back()->with('success', 'Assignment was updated successfully.');
    }
    public function updateAssigned(Request $request, $id)
    {

       /*  [
            'assignment' => sprintf('%s </br> %s', $request->get('old_assignment'), $request->input('assignment')),
            'description' => sprintf('%s </br> %s', $request->get('old_description'), $request->input('description')),
            'to' => $request->input('to'),
            'status' => $request->input('progress'),
            'evidence' => sprintf('%s </br> %s', $request->get('old_evidence'), $request->input('evidence')),
            'supervisor_comments' => sprintf('%s </br> %s', $request->get('old_supervisor_comments'), $request->input('supervisor_comments')),
        ];*/
        try {
            $assignment = Assigned::query()->find($id);

            $assignment->update([
                'assignment' => $request->input('assignment'),
                'description' =>  $request->input('description'),
                'to' => $request->input('to'),
                'status' => $request->input('progress'),
                'evidence' => sprintf('%s </br> %s', $request->get('old_evidence'), $request->input('evidence')),
                'supervisor_comments' => sprintf('%s </br> %s', $request->get('old_supervisor_comments'), $request->input('supervisor_comments')),
            ]);

            // Upload any files the user had uploaded
            DataController::filesUploader($request, $request->user(), $assignment);
        } catch (Exception $exception) {
            //dd();
            //return $exception;
            return redirect()->back()->with('error', 'Sorry. Something went wrong');
        }
       $user = auth()->user();

        $supervisor = User::query()->find($user->supervisor_id, [
            'id', 'email',
        ]);


        $assignment = Assigned::query()->find($id, ['status']);

        if (!is_null($supervisor->email) && $assignment->status == 'completed') {
            info('sending email now..');
            // Send the notification here
            Notification::send($supervisor, new CompleteAssignment($user));
        }

        return redirect()->back()->with('success', 'Assignment was updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $assignment = Assigned::query()->find($id);
        $assignment->delete();
        return redirect()->route('assignments.assigned-supervisor')
            ->with('success', 'Assignment deleted successfully');
    }
}
