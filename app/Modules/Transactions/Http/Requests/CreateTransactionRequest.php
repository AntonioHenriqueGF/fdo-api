<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tra_description' => 'required|string|max:255',
            'tra_amount' => 'required|numeric',
            'tra_date' => 'required|date',
        ];
    }
}
