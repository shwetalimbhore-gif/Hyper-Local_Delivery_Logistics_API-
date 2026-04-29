<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Rider;

class ParcelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_email' => 'nullable|email',
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_email' => 'nullable|email',
            'receiver_address' => 'required|string',
            'parcel_name' => 'required|string|max:255',
            'parcel_description' => 'nullable|string',
            'weight' => 'required|numeric|min:0.1',
            'size' => 'required|numeric|min:0.1',
            'parcel_type' => 'nullable|string',
            'delivery_charge' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'source_hub_id' => 'required|exists:hubs,id',
            'assigned_rider_id' => 'nullable|exists:riders,id',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sender_name.required' => 'Sender name is required',
            'sender_phone.required' => 'Sender phone number is required',
            'sender_address.required' => 'Sender address is required',
            'receiver_name.required' => 'Receiver name is required',
            'receiver_phone.required' => 'Receiver phone number is required',
            'receiver_address.required' => 'Receiver address is required',
            'parcel_name.required' => 'Parcel name is required',
            'weight.required' => 'Parcel weight is required',
            'weight.min' => 'Weight must be at least 0.1 kg',
            'size.required' => 'Parcel size is required',
            'size.min' => 'Size must be at least 0.1 cm³',
            'delivery_charge.required' => 'Delivery charge is required',
            'delivery_charge.min' => 'Delivery charge must be at least 0',
            'source_hub_id.required' => 'Source hub is required',
            'source_hub_id.exists' => 'Selected hub does not exist',
            'assigned_rider_id.exists' => 'Selected rider does not exist',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $riderId = $this->input('assigned_rider_id');
            $hubId = $this->input('source_hub_id');

            if (!$riderId || !$hubId) {
                return;
            }

            $sameHub = Rider::where('id', $riderId)
                ->where('hub_id', $hubId)
                ->exists();

            if (!$sameHub) {
                $validator->errors()->add('assigned_rider_id', 'Selected rider must belong to the selected source hub.');
            }
        });
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
