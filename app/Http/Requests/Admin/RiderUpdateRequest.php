<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RiderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $riderId = $this->route('rider');

        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'hub_id' => 'required|exists:hubs,id',
            'vehicle_type' => 'required|in:bike,scooter,bicycle,car,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'max_weight_capacity' => 'nullable|numeric|min:0',
            'max_size_capacity' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,busy,offline',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Rider name is required',
            'phone.required' => 'Phone number is required',
            'hub_id.required' => 'Hub assignment is required',
            'hub_id.exists' => 'Selected hub does not exist',
            'vehicle_type.required' => 'Vehicle type is required',
            'vehicle_type.in' => 'Invalid vehicle type selected',
            'status.required' => 'Rider status is required',
            'status.in' => 'Invalid status selected',
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
