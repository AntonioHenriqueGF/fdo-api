<?php

namespace App\Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCategoriesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
        ];
    }
}
