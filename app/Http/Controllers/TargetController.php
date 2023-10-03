<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTargetRequest;
use App\Target;
use App\User;
use App\Project;
use App\Activity;
use App\Department;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\TargetUpdate;

class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        // Get the auth user
        $user = $request->user();

        // Get the user targets with the files uploaded
        $targets = Target::with(
            'fileUploads:id,filename,public_url,uploadable_type,uploadable_id',
            'updates'
        )->whereIn('user_id', [$user->id])->paginate(20);

        return view('targets.index', compact('targets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $projects=Project::get();
        $activities=Activity::get();
        $departments=Department::get();
        return view('targets.create',compact('projects','activities','departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTargetRequest $request
     * @return RedirectResponse
     */
    public function store(CreateTargetRequest $request)
    {
        // Get the auth user
        $user = $request->user();

        // Create a new target
        try {
            $target = Target::query()->create([
                'target' => ucfirst($request->input('target')),
                'sno' => ucfirst($request->input('sno')),
                'performance_indicator' => ucfirst($request->input('performance_indicator')),
                //'self_appraisal' => $request->input('self_appraisal'),
                //'actual_appraisal' => $request->input('actual_appraisal'),
                'evidence' => ucfirst($request->input('evidence')),
                'projects' => ucfirst($request->input('projects')),
                'activity' => ucfirst($request->input('activity')),
                'department' => ucfirst($request->input('department')),
                'user_id' => $user->id,
            ]);

            // Check if the user has uploaded a file
            if ($request->hasFile('files')) {
                DataController::filesUploader($request, $user, $target);
            }
        } catch (Exception $exception) {
            return $exception;
            //return redirect()->back()->with('error', 'Sorry something went wrong. Please try again.')
                //->withInput($request->all());
        }

        return redirect()->back()->with('success', 'Target has been created successflily.');
    }

    public function show(Request $request, int $id)
    {
        // Get the user
        $user = User::query()->find($id);

        // Get the auth user
        $authUser = $request->user();

        // Find the user using the id, select their work plans
        $targets = Target::with('fileUploads')->where('user_id', $id)->paginate(15);

        // Set the is viewing action
        !$authUser->is_supervisor && $authUser->id != $id ?: $request->query->set('is_viewing', false);

        // Check if the auth user is the selected user supervisor
        if ($request->user()->id != $user->supervisor_id) {
            return redirect()->back()->with('error', 'The selected action is not allowed.');
        }

        return view('targets.index', compact('targets', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param Target $target
     * @return Factory|RedirectResponse|View
     */
    public function edit(Request $request, Target $target)
    {
        // Keep track of commenting feature
        $canComment = false;

        // Check if the user can updates
        if ($request->query->has('_can_comment')) {
            $canComment = true;

            return view('targets.edit', compact('target', 'canComment'));
        }

        // Check if the target belongs to the user
        if ($request->user()->id != $target->user_id) {
            return redirect()->back()->with('error', 'You can only view your targets.');
        }

        return view('targets.edit', compact('target', 'canComment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // return $request;
        // Get the auth user
        $user = $request->user();

        // Update the target data
        try {
            $target = Target::query()->find($id);

            Target::query()->where('id', $id)->update([
                'target' => ucfirst($request->input('target')),
                'performance_indicator' => ucfirst($request->input('performance_indicator')),
                'self_appraisal' => $request->input('self_appraisal'),
                'actual_appraisal' => $request->input('actual_appraisal'),
                'evidence' => ucfirst($request->input('evidence')),
                'supervisee_comments' => ucfirst($request->input('supervisee_comments')),
                //'mid_remarks' => ucfirst($request->input('mid_remarks')),


            ]);


            // Check if the user has uploaded a file
            if ($request->hasFile('files')) {
                DataController::filesUploader($request, $user, $target);
            }
        } catch (Exception $exception) {
            return redirect()->back()->with('error', 'Sorry something went wrong. Please try again.')
                ->withInput($request->all());
        }

        return redirect()->back()->with('success', 'Target has been updated successflily.');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }

    public function commentOnTarget(Request $request, int $id)
    {
        try {
            // Create the target update
            TargetUpdate::create([
                'supervisor_comments' => $request->input('supervisor_comments'),
                'mid_year_remarks' => $request->input('mid_year_remarks'),
                'approval' => $request->input('approval'),
                'target_id' => $id,
                'user_id' => $request->get('user_id'),
            ]);
        } catch (Exception $exception) {
            dd($exception->getMessage());
            return redirect()->back()->with('error', 'Sorry something went wrong. Please try again.')
                ->withInput($request->all());
        }

        return redirect()->back()->with('success', 'Comment added successflily.');
    }public function up()
    {
        Schema::table('nvm', function (Blueprint $table) {
            $table->renameColumn('data1', 'call_owner');
            $table->renameColumn('data2', 'transfer_type');
            $table->string('transfer_data', 50)->nullable();
        });
    }

 public function down()
    {
        Schema::table('nvm', function (Blueprint $table) {
            $table->dropColumn('transfer_data');
            $table->renameColumn('call_owner', 'data1');
            $table->renameColumn('transfer_type', 'data2');
        });
    }

    public function reportGenerator(Request $request)
    {
        // Load the user work plans with each work plan latest update
        //$user = $request->user()->load('targets');

        return view('staff.target-report');
    }
}
