<?php

namespace App\Modules\Imports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users to make this request, adjust as needed
    }

    public function rules(): array
    {
        // Every string field must have a maximum length of 255 characters, and every numeric field must be a valid number. Adjust the rules as needed based on your requirements.
        return [
            'fileName' => 'required|string|max:255',
            'fileHash' => 'required|string|max:255',
            'normalized' => 'required|array',
            'normalized.*.amount' => 'numeric',
            'normalized.*.credit_only' => 'numeric',
            'normalized.*.debit_only' => 'numeric',
            'normalized.*.closing_balance' => 'numeric',
            'normalized.*.date_yyyymmdd' => 'string|max:255',
            'normalized.*.description' => 'string|max:255',
        ];
    }
}
