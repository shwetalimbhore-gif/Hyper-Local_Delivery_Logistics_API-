<?php

namespace App\Http\Requests\Rider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateParcelStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Let controller handle authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status_id' => 'required|exists:parcel_statuses,id',
            'failure_reason' => 'required_if:status_id,6|nullable|string',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status_id.required' => 'Status ID is required',
            'status_id.exists' => 'Invalid status selected',
            'failure_reason.required_if' => 'Failure reason is required when delivery fails',
            'failure_reason.string' => 'Failure reason must be a string',
            'notes.string' => 'Notes must be a string',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
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
