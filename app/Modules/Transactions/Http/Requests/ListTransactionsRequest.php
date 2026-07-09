<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTransactionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'category_id' => 'nullable|integer|exists:categories,cat_id',
            'rule_id' => 'nullable|integer|exists:rules,rul_id',
            'limitStart' => 'nullable|numeric',
            'limitEnd' => 'nullable|numeric|gte:limitStart',
        ];
    }
}
