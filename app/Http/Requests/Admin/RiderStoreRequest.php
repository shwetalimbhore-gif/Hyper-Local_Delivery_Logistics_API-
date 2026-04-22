<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RiderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8',
            'hub_id' => 'required|exists:hubs,id',
            'employee_id' => 'required|string|unique:riders,employee_id',
            'vehicle_type' => 'required|in:bike,scooter,bicycle,car,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'max_weight_capacity' => 'nullable|numeric|min:0',
            'max_size_capacity' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Rider name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'Email address already exists',
            'phone.required' => 'Phone number is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'hub_id.required' => 'Hub assignment is required',
            'hub_id.exists' => 'Selected hub does not exist',
            'employee_id.required' => 'Employee ID is required',
            'employee_id.unique' => 'Employee ID already exists',
            'vehicle_type.required' => 'Vehicle type is required',
            'vehicle_type.in' => 'Invalid vehicle type selected',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
