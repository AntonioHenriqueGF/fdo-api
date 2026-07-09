<?php

namespace App\Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryIncomeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cat_is_income' => 'required|boolean',
        ];
    }
}
