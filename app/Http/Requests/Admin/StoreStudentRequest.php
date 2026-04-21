<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roll_number' => 'required|unique:students,roll_number',
            'gtu_enrollment_no' => 'required|string|max:50|unique:students,gtu_enrollment_no',
            'course_id' => 'required|exists:courses,id',
            'current_year' => 'nullable|integer|min:1|max:10',
            'admission_year' => 'nullable|integer|min:2000|max:2100',
            'phone' => 'nullable|digits:10',
            'address' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseId = $this->input('course_id');
            $currentYear = $this->input('current_year', 1);

            if ($courseId) {
                $course = \App\Models\Course::find($courseId);
                if ($course && ($currentYear < 1 || $currentYear > (int) $course->duration_years)) {
                    $validator->errors()->add('current_year', "Current year must be between 1 and {$course->duration_years} for {$course->name}.");
                }
            }
        });
    }
}
