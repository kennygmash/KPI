<?php

namespace App\Http\Controllers;
use App\FileUpload;
use App\GeneralFileUpload;
use App\User;
use App\Project;
use App\Activity;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use DB;


class ActivityController extends Controller
{
    public function activityView(){


    $projects=Project::get();
    //$projects = DB::table('projects')->select('id', DB::raw("CONCAT(projects.project_name,' ',projects.project_no) AS full_name"))
        //->get()->pluck('full_name', 'id');
    //return dd($projects);
     return view('activities.create',compact('projects'));
    }

    public function store(Request $request)
    {
        try {
            // Create a new work plan
            Activity::query()->create([
                'activity_name' => $request->input('activity_name'),
                'activity_no' => $request->input('activity_no'),
                'projects' => $request->input('projects'),
                'activity' => $request->input('activity'),
                'notes' => $request->input('notes'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'user_id' => auth()->id(),
            ]);
        } catch (Exception $exception) {
            return  $exception;
            //return redirect()->back()->with('error', 'Sorry something went wrong. PLease try again.')
               // ->withInput($request->all());
        }
;
        return redirect()->back()->with('success', 'The activity was successfully created.');
}
    public function index(Request $request){
        $activities=DB::table('activities')->get();

        return view('activities.view-activities', compact('activities'));

    }

    public function update(Request $request, $id)
      {

              try {
                  $activity = Activity::query()->find($id);

                  $activity->update([
                      'start_date' => $request->input('start_date'),
                      'end_date' => $request->input('end_date'),
                      //'progress' => $request->input('progress'),
                      //'evidence' => sprintf('%s </br> %s', $request->get('old_evidence'), $request->input('evidence')),
                  ]);

                  // Upload any files the user had uploaded
                  //DataController::filesUploader($request, $request->user(), $assignment);
              } catch (Exception $exception) {
                  return $exception;
                  //return redirect()->back()->with('error', 'Sorry. Something went wrong');
              }
             /* $user = auth()->user();

              $supervisor = User::query()->find($user->supervisor_id, [
                  'id', 'email',
              ]);


              $assignment = Assignments::query()->find($id, ['progress']);

              if (!is_null($supervisor->email) && $assignment->progress == 'completed') {
                  info('sending email now..');
                  // Send the notification here
                  Notification::send($supervisor, new CompleteAssignment($user));
              } */

              return redirect()->back()->with('success', 'Activity was updated successfully.');

      }
      public function edit($id)
      {
          $activity = Activity::query()->find($id);

          return view('activities.edit', compact('activity'));
  }

  public function showUserActivities(Request $request, int $userID)
  {
      // Get the auth user
      $authUser = $request->user();

      // Set the is viewing action
      !$authUser->is_supervisor && $authUser->id != $userID ?: $request->query->set('is_viewing', false);

      // Get my assignments
      $activities = Activity::query()->where('user_id', $userID)
          ->paginate(30);

      //$types = $this->assignmentTypes();

      // Get the user
      $user = User::query()->find($userID);

      return view('activities.view-activities', compact('activities', 'user'));
  }



}
