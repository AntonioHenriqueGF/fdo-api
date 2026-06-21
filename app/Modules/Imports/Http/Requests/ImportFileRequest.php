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
        return [
            'fileName' => 'required|string',
            'fileHash' => 'required|string',
            'normalized' => 'required|array',
            'normalized.*.amount' => 'numeric',
            'normalized.*.credit_only' => 'numeric',
            'normalized.*.debit_only' => 'numeric',
            'normalized.*.closing_balance' => 'numeric',
            'normalized.*.date_yyyymmdd' => 'string',
            'normalized.*.description' => 'string',
        ];
    }
}
