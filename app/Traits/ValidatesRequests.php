<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ValidatesRequests
{
    /**
     * Validate request data
     */
    protected function validateRequest($data, $rules, $messages = [])
    {
        return Validator::make($data, $rules, $messages);
    }

    /**
     * Get validation error response
     */
    protected function validationErrorResponse($validator)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }
}
