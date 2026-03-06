<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GenerateAutoTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'semester_type' => ['required', 'in:odd,even'],
            'selected_years' => ['nullable', 'array'],
            'selected_years.*' => ['integer', 'min:1', 'max:4'],
            'selected_teacher_ids' => ['nullable', 'array'],
            'selected_teacher_ids.*' => ['integer', 'exists:teachers,id'],
            'selected_classroom_ids' => ['nullable', 'array'],
            'selected_classroom_ids.*' => ['integer', 'exists:classrooms,id'],
        ];
    }
}
