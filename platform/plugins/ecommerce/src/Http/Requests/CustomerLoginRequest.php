<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomerCreateRequest extends Request
{
    public function authorize(): bool
    {
        return true; // Allow all users to use this request
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
