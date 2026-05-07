<?php

namespace App\Http\Requests\Admin;

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
        // Only admin can access (add your admin check)
        $user = $this->user();
        return $user && $user->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status_id' => 'required|exists:parcel_statuses,id',
            'failure_reason' => 'required_if:status_id,with_failed_delivery|nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status_id.required' => 'Please select a status to update.',
            'status_id.exists' => 'The selected status is invalid.',
            'failure_reason.required_if' => 'Please provide a reason for the failed delivery.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $statusId = $this->input('status_id');

            if (!$statusId) {
                return;
            }

            $status = \App\Models\ParcelStatus::find($statusId);

            if ($status && $status->slug === 'failed-delivery' && !$this->input('failure_reason')) {
                $validator->errors()->add(
                    'failure_reason',
                    'Please provide a reason for the failed delivery.'
                );
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
