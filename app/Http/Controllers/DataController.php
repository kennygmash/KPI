<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Department;
use App\Designation;
use App\FileUpload;
use App\JobGroup;
use App\User;
use App\WorkPlan;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use DB;

class DataController extends Controller
{
    /**
     * Here we process the csv file uploaded. We will convert it to an array
     * @param object $file
     * @return bool|false|string|string[]|null
     */
    private function processCSVFiles(object $file)
    {
        return mb_convert_encoding(array_map('str_getcsv', file($file->getRealPath())), 'UTF-8', 'UTF-8');
    }

    /**
     * Show the view for loading the supervisors CSV
     * @return Factory|View
     */
    public function loadSupervisorsView()
    {
        return view('data-processors.supervisors');
    }

    public function loadEmployees(Request $request)
    {
         $users = User::with(
            'jobGroup:id,name', 'designation:id,name', 'department:id,name', 'campus:id,name', 'supervisor:id,name'
        )->get([
                'id', 'name', 'payroll_number', 'job_group_id', 'designation_id', 'department_id', 'campus_id', 'supervisor_id'
            ]);
        //$users=User::get();

        //dd();
        return view('data-processors.employees',compact('users'));
    }
    /**
     * Add the user to the system
     * @param array $user
     */
    private function createUser(array $user)
    {
        User::query()->create([
            'name' => ucwords($user['name']),
            'is_supervisor' => $user['isSupervisor'],
            'payroll_number' => $user['payrollNumber'],
            'job_group_id' => $user['jobGroupID'],
            'designation_id' => $user['designationID'],
            'department_id' => $user['departmentID'],
            'campus_id' => $user['campusID'],
            'supervisor_id' => $user['supervisorID'],
        ]);
    }

    /**
     * Find the job group using the slug
     * @param string $slug
     * @return Builder|Model|object|null
     */
    private function getJobGroup(string $slug)
    {
        return JobGroup::query()->where('slug', $slug)->first(['id']);
    }

    /**
     * Find the designation using the slug
     * @param string $slug
     * @return Builder|Model|object|null
     */
    private function getDesignation(string $slug)
    {
        return Designation::query()->where('slug', $slug)->first(['id']);
    }

    /**
     * Find the department using the slug
     * @param string $slug
     * @return Builder|Model|object|null
     */
    private function getDepartment(string $slug)
    {
        return Department::query()->where('slug', $slug)->first(['id']);
    }

    /**
     * Find the campus using the slug
     * @param string $slug
     * @return Builder|Model|object|null
     */
    private function getCampus(string $slug)
    {
        return Campus::query()->where('slug', $slug)->first(['id']);
    }

    private function getSupervisor(string $name)
    {
        return User::query()->where('name', $name)->first(['id']);
    }

    // Check if we have existing users
    private function userExists(string $payrollNumber)
    {
        // Find the user using the payroll number
        $users = User::query()->whereIn('payroll_number', [$payrollNumber])->count('id');

        return $users >= 1 ? true : false;
    }

    /**
     * Create the supervisors data using the uploaded file data
     * @param array $data
     * @param bool $isSupervisor
     * @return array
     */
    private function createUsersDataFromFile(array $data, bool $isSupervisor)
    {
        // Get the payroll number
        $payrollNumber = $data['payrollNumber'];

        // Get the job group and check if it exists
        $jobGroup = $this->getJobGroup($data['jobGroup']);

        if (!$jobGroup) {
            return [
                'success' => false,
                'message' => sprintf("Job group %s does not exist. Please add it.", $data['jobGroup']),
            ];
        }

        // Get the designation and check if it exists
        $designation = $this->getDesignation($data['designation']);

        if (!$designation) {
            return [
                'success' => false,
                'message' => sprintf("Designation %s does not exist. Please add it.", $data['designation']),
            ];
        }

        // Get the department and check if it exists
        $department = $this->getDepartment($data['department']);

        if (!$department) {
            return [
                'success' => false,
                'message' => sprintf("Department %s does not exist. Please add it.", $data['department']),
            ];
        }

        // Get the campus and check if it exists
        $campus = $this->getCampus($data['campus']);

        if (!$campus) {
            return [
                'success' => false,
                'message' => sprintf("Campus %s does not exist. Please add it.", $data['campus']),
            ];
        }

        // Check the type of user we are creating
        $supervisorID = null;

        // Get the supervisor for the staff user
        if (!$isSupervisor) {
            $supervisor = $this->getSupervisor($data['supervisor']);

            if (!$supervisor) {
                return [
                    'success' => false,
                    'message' => sprintf("Supervisor %s does not exist. Please add them.", $data['supervisor']),
                ];
            }

            $supervisorID = $supervisor->id;
        }

        // Check if the user exists, if they do ignore and pass
        if (!$this->userExists($payrollNumber)) {
            // Add the user to the system
            $this->createUser([
                'name' => $data['name'],
                'isSupervisor' => $isSupervisor,
                'payrollNumber' => $payrollNumber,
                'jobGroupID' => $jobGroup->id,
                'designationID' => $designation->id,
                'departmentID' => $department->id,
                'campusID' => $campus->id,
                'supervisorID' => $supervisorID,
            ]);
        } else {
            User::query()->where('payroll_number', $payrollNumber)->update([
                'supervisor_id' => $supervisorID
            ]);
        }

        return [
            'success' => true,
            'message' => 'Validation passed.',
        ];
    }

    private function validateCSVFileFormat(Request $request, string $key)
    {
        // Process the file
        $contents = $this->processCSVFiles($request->file('file'));

        // Get the header rows
        $headers = array_slice($contents, 0, 1)[0];

        $fileConfig = config('system.files.' . $key);

        $compareArrays = array_diff($headers, $fileConfig);

        if (count($compareArrays)) {
            return [
                'success' => false,
                'message' => 'Please use the expected format on the CSV file.',
                'contents' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Validation passed.',
            'contents' => $contents,
        ];
    }

    // Upload the supervisors file
    public function uploadSupervisorsCSV(Request $request)
    {
        // Extract the request data
        $isSupervisor = (bool)$request->get('_is_supervisor');

        // Attempt to validate the file
        $validatedFile = $this->validateCSVFileFormat($request, 'supervisors');

        if (!$validatedFile['success']) {
            return redirect()->back()->with('error', $validatedFile['message']);
        }

        $departments = [];

        foreach ($validatedFile['contents'] as $key => $content) {
            if (is_numeric($content[0])) {
                // Extract the file data
                $data = [
                    'payrollNumber' => $content[0],
                    'name' => $content[1],
                    'jobGroup' => Str::slug($content[2]),
                    'designation' => Str::slug($content[3]),
                    'department' => Str::slug($content[4]),
                    'campus' => Str::slug($content[5]),
                ];

                // Create the users
                $response = $this->createUsersDataFromFile($data, $isSupervisor);

                $departments[] = $response;

                if (!$response['success']) {
                    return redirect()->back()->with('error', $response['message']);
                }
            }
        }

        return redirect()->back()->with('success', 'File processed successflily.');
    }

    /**
     * Show the view for loading the supervisors CSV
     * @return Factory|View
     */
    public function loadStaffView()
    {
        // Load the campuses
        $campuses = Campus::query()->get(['name', 'slug']);

        return view('data-processors.staff', compact('campuses'));
    }

    public function uploadStaffCSV(Request $request)
    {
        // Attempt to validate the file
        $validatedFile = $this->validateCSVFileFormat($request, 'staff');

        if (!$validatedFile['success']) {
            return redirect()->back()->with('error', $validatedFile['message']);
        }

        // Extract the request data
        $campus = $request->input('campus');

        $errors = $departments = [];

        foreach ($validatedFile['contents'] as $key => $content) {
            if (is_numeric($content[0])) {
                // Extract the file data
                $data = [
                    'payrollNumber' => $content[0],
                    'name' => $content[1],
                    'jobGroup' => Str::slug($content[2]),
                    'designation' => Str::slug($content[3]),
                    'department' => Str::slug($content[4]),
                    'campus' => $campus,
                    'supervisor' => $content[5],
                ];

                // Create the users
                $response = $this->createUsersDataFromFile($data, false);

                $departments[] = $response;
//
                if (!$response['success']) {
                    $errors[] = $response['message'];
                    continue;
//                    return redirect()->back()->with('error', $response['message']);
                }
            }
        }
        return $errors;
        return redirect()->back()->with('success', 'File processed successflily.');
    }

    /**
     * Upload the files the user had selected. We store the file name in the DB
     * @param Request $request
     * @param User $user
     * @param $model
     * @param bool $isDepartmental
     * @param  bool $isCounty
     */
    public static function filesUploader(Request $request, User $user, $model, bool $isDepartmental,
    bool $isCounty = false, bool $isCountyTarget = false)
    {
        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $filenameWithExtension = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileToStore = Str::slug($user->name . '_' . $user->payroll_number . '_' . $filename . '_' . time()) . '.' . $extension;

                $file->storeAs('public/user-uploads', $fileToStore);

                // Create a new file
                $fileUpload = new FileUpload();
                $fileUpload->filename = $fileToStore;
                $fileUpload->path = storage_path('app/' . 'public/user-uploads/' . $fileToStore);
                $fileUpload->public_url = asset('storage/user-uploads/' . $fileToStore);
                $fileUpload->user_id = $user->id;
                $fileUpload->is_departmental = $isDepartmental;
                $fileUpload->is_county = $isCounty;
                $fileUpload->is_county_target = $isCountyTarget;

                $model->fileUploads()->save($fileUpload);
            }
        }
    }

    public function countyFiles(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user();

        // Get county files
        $countyFiles = FileUpload::query()
            ->where('is_county', true)
            ->orderByDesc('created_at')
            ->get(['public_url', 'filename', 'created_at']);


        return view('staff.county-files-all', compact('countyFiles'));
    }

    public function countyTargets(Request $request)
        {
            // Load the user work plans with each work plan latest update
            $user = $request->user();

            // Get county files
            $countyTargets = FileUpload::query()
                ->where('is_county_target', true)
                ->orderByDesc('created_at')
                ->get(['public_url', 'filename', 'created_at']);


            return view('staff.county-targets-all', compact('countyTargets'));
        }

    public function countySector(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user();

        // Get county files
        $countyTargets = FileUpload::query()
            ->where('is_county_target', true)
            ->orderByDesc('created_at')
            ->get(['public_url', 'filename', 'created_at']);


        return view('staff.sector-all', compact('countyTargets'));
    }

    public function countyProject(Request $request)
    {
        // Load the user work plans with each work plan latest update
        $user = $request->user();

        // Get county files
        $countyTargets = FileUpload::query()
            ->where('is_county_target', true)
            ->orderByDesc('created_at')
            ->get(['public_url', 'filename', 'created_at']);


        return view('staff.project-all', compact('countyTargets'));
    }

}
