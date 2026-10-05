<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thesis_project_id' => 'required|exists:thesis_projects,id',
            'type' => 'required|string',
            'schedule_start' => 'required|date',
            'schedule_end' => 'required|date|after:schedule_start',
            'location' => 'required|string',
        ];
    }
}
