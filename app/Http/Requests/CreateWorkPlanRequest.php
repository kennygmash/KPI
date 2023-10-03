<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rlies that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key_result' => ['required', 'string'],
            'strategic_objective' => ['required', 'string'],
            'other_objectives' => ['required', 'string'],
            'activity' => ['required', 'string'],
            'expected_output' => ['required', 'string'],
            'performance_indicator' => ['required', 'string'],
            'resources_required' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'time_frame' => ['required', 'string'],
            'assumptions' => ['required', 'string'],
        ];
    }
}
