<?php

namespace App\Http\Requests\Hod;

use Illuminate\Foundation\Http\FormRequest;

class UpsertTeacherAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('hod');
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'academic_year' => ['required', 'integer', 'min:1', 'max:8'],
            'subject_teacher_map' => ['required', 'array', 'min:1'],
            'subject_teacher_map.*' => ['required', 'integer', 'exists:teachers,id'],
        ];
    }
}
