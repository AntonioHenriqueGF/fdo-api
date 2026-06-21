<?php

namespace App\Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pattern' => 'required|string|max:255',
            'priority' => 'required|integer|min:1|max:5',
        ];
    }
}
