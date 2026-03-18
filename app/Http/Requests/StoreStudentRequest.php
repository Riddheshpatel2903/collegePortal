<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'roll_number' => 'required|string|max:50|unique:students,roll_number',
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'current_year' => 'required|integer|min:1',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'student_status' => 'nullable|string',
            'academic_status' => 'nullable|string',
        ];
    }
}
