<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignSupervisorRequest extends FormRequest
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
            'supervisor_profile_id' => 'sometimes|exists:supervisor_profiles,id',
            'role' => 'sometimes|in:primary,secondary',
            'supervisors' => 'sometimes|array',
            'supervisors.*' => 'exists:supervisor_profiles,id',
            'action' => 'sometimes|string|in:redistribute',
        ];
    }
}
