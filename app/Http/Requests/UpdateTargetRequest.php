<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkPlanRequest extends FormRequest
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
    public function rlies()
    {
        if (!$this->request->has('_can_comment')) {
            return [
                'evidence' => ['required', 'string'],
                'progress' => ['required', 'string'],
            ];
        }

        return [
            'supervisor_comment' => ['string', 'nullable']
        ];
    }
}
