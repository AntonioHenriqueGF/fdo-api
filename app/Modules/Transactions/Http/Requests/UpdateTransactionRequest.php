<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tra_description' => 'sometimes|required|string|max:255',
            'tra_amount' => 'sometimes|required|numeric',
            'tra_date' => 'sometimes|required|date',
        ];
    }
}
