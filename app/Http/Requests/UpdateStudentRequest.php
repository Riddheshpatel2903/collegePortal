<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|unique:students,email,{$studentId}",
            'roll_number' => "sometimes|required|string|max:50|unique:students,roll_number,{$studentId}",
            'course_id' => 'sometimes|required|exists:courses,id',
            'department_id' => 'sometimes|required|exists:departments,id',
            'academic_session_id' => 'sometimes|required|exists:academic_sessions,id',
            'current_year' => 'sometimes|required|integer|min:1',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
        ];
    }
}
