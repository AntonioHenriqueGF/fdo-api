<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'dba_date' => 'required|date',
            'dba_closing_balance' => 'required|numeric',
        ];
    }
}
