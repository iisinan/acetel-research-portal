<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'content' => 'required_without:file|nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:20480',
            'thesis_project_id' => 'required|exists:thesis_projects,id',
            'student_milestone_id' => 'nullable|exists:student_milestones,id',
            'reply_to_id' => 'nullable|exists:messages,id',
        ];
    }
}
