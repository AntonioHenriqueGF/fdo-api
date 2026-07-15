<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'dba_date' => 'sometimes|date',
            'dba_closing_balance' => 'sometimes|numeric',
        ];
    }
}
