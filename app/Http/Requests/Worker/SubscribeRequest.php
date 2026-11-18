<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                'string',
                Rule::in(array_keys(config('payments.methods'))),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Choisissez un moyen de paiement.',
            'payment_method.in' => 'Moyen de paiement non supporté.',
        ];
    }
}
