<?php

namespace App\Modules\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer|distinct|exists:categories,cat_id',
        ];
    }
}
