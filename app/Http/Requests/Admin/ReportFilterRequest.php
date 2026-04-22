<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'hub_id' => 'nullable|exists:hubs,id',
            'rider_id' => 'nullable|exists:riders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'period.in' => 'Invalid period selected',
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'hub_id.exists' => 'Selected hub does not exist',
            'rider_id.exists' => 'Selected rider does not exist',
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
