<?php

namespace App\Http\Controllers;
use App\FileUpload;
use App\GeneralFileUpload;
use App\User;
use App\Project;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use DB;

class ProjectController extends Controller
{
      public function store(Request $request)
          {
              try {
                  // Create a new work plan
                  Project::query()->create([
                      'project_name' => $request->input('project_name'),
                      'project_no' => $request->input('project_no'),
                      'department' => $request->input('department'),
                      'project_manager' => $request->input('project_manager'),
                      'activity' => $request->input('activity'),
                      'notes' => $request->input('notes'),
                      'due_date' => $request->input('due_date'),
                      'time_frame' => $request['time_frame'],
                      'user_id' => auth()->id(),
                  ]);
              } catch (Exception $exception) {
                  return  $exception;
                  //return redirect()->back()->with('error', 'Sorry something went wrong. PLease try again.')
                     // ->withInput($request->all());
              }
      ;
              return redirect()->back()->with('success', 'The project was successfully created.');
      }

      public function index()
      {

              //$projects = Project::get();
              $projects = DB::table('projects')->get();
              //return dd($projects);
              return view('projects.view-projects', compact('projects'));
      }

       public function editView($id)
          {
              $project = Project::query()->find($id);

              return view('projects.edit', compact('project'));
      }

      public function update(Request $request, $id)
      {

              try {
                  $project = Project::query()->find($id);

                  $project->update([
                      'due_date' => $request->input('due_date'),
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

              return redirect()->back()->with('success', 'Project was updated successfully.');

      }

}
