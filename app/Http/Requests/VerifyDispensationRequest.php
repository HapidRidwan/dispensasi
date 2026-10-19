<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDispensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isGuru();
    }

    public function rules(): array
    {
        return [
            'approved' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'required_if:approved,false'],
        ];
    }
}
