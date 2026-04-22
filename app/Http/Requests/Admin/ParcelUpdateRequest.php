<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ParcelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_name' => 'sometimes|string|max:255',
            'sender_phone' => 'sometimes|string|max:20',
            'sender_email' => 'nullable|email',
            'sender_address' => 'sometimes|string',
            'receiver_name' => 'sometimes|string|max:255',
            'receiver_phone' => 'sometimes|string|max:20',
            'receiver_email' => 'nullable|email',
            'receiver_address' => 'sometimes|string',
            'parcel_name' => 'sometimes|string|max:255',
            'parcel_description' => 'nullable|string',
            'weight' => 'sometimes|numeric|min:0.1',
            'size' => 'sometimes|numeric|min:0.1',
            'parcel_type' => 'nullable|string',
            'delivery_charge' => 'sometimes|numeric|min:0',
            'payment_method' => 'nullable|string',
            'source_hub_id' => 'sometimes|exists:hubs,id',
            'assigned_rider_id' => 'nullable|exists:riders,id',
            'status_id' => 'sometimes|exists:parcel_statuses,id',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'weight.min' => 'Weight must be at least 0.1 kg',
            'size.min' => 'Size must be at least 0.1 cm³',
            'delivery_charge.min' => 'Delivery charge must be at least 0',
            'source_hub_id.exists' => 'Selected hub does not exist',
            'assigned_rider_id.exists' => 'Selected rider does not exist',
            'status_id.exists' => 'Selected status does not exist',
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
