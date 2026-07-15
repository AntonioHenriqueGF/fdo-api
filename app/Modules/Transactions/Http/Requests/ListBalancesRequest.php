<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListBalancesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'limitStart' => 'nullable|numeric',
            'limitEnd' => 'nullable|numeric',
        ];
    }
}
