<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTargetRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'target' => ['string', 'required'],
            'performance_indicator' => ['string', 'required'],
            'projects' => ['string', 'required'],
            'activity' => ['string', 'required'],
            'department' => ['string', 'required'],
            'sno' => ['string', 'required'],
            //'self_appraisal' => ['integer', 'required', 'min:0', 'max:100'],
            //'actual_appraisal' => ['integer', 'required', 'min:0', 'max:100'],
            'evidence' => ['string', 'required'],
            'files' => ['nullable'],
        ];
    }
}
