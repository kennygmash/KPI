<?php

namespace App\Http\Controllers;

use App\Target;
use App\User;
use App\Project;
use App\Activity;
use App\Department;
use App\Training;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use DB;

class TrainingController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Create a new work plan
            Training::query()->create([
                'training' => $request->input('training'),
                'projects' => $request->input('projects'),
                'activity' => $request->input('activity'),
                'department' => $request->input('department'),
                'date' => $request->input('date'),
                'year' => $request->input('year'),
                'user_id' => auth()->id(),
            ]);
        } catch (Exception $exception) {
            return  $exception;
            //return redirect()->back()->with('error', 'Sorry something went wrong. PLease try again.')
               // ->withInput($request->all());
        }
;
        return redirect()->back()->with('success', 'The training entry was successfully created.');
    }

    public function index()
    {
        // Extract the request data

        /* $training = Training::query();


        $trainings = $training->with(
             'department:id,name'
        )->first([
            'training', 'projects', 'activity', 'department', 'approval', 'date', 'year', 'user_id'
        ]); */

            //$projects = Project::get();
            $trainings = DB::table('trainings')->get();
            //return dd($projects); 
            return view('staff.view-trainings', compact('trainings'));
    }

    public function editView($id)
    {
        $training = Training::query()->find($id);

        return view('staff.edit-training', compact('training'));
    }

    public function update(Request $request, $id)
      {

              try {
                  $training = Training::query()->find($id);

                  $training->update([
                      'training' => $request->input('training'),
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

              return redirect()->back()->with('success', 'Training was updated successfully.');

      }

      public function showUserTraining(Request $request, int $userID)
  {
      // Get the auth user
      $authUser = $request->user();

      // Set the is viewing action
      !$authUser->is_supervisor && $authUser->id != $userID ?: $request->query->set('is_viewing', false);

      // Get my assignments
      $trainings = Training::query()->where('user_id', $userID)
          ->paginate(30);

      //$types = $this->assignmentTypes();

      // Get the user
      $user = User::query()->find($userID);

      return view('staff.view-trainings', compact('trainings', 'user'));
  }
    
}
