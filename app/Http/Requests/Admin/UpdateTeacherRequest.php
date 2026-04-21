<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
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
        $userId = $this->route('teacher')->user_id;

        return [
            'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|digits:10',
            'qualification' => 'nullable|string|max:255',
        ];
    }
}
