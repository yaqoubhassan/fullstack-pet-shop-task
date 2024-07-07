<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|required|string|in:credit_card,cash_on_delivery,bank_transfer',
            'details' => 'sometimes|required|array',
            'details.holder_name' => 'required_if:type,credit_card|string',
            'details.number' => 'required_if:type,credit_card|string',
            'details.ccv' => 'required_if:type,credit_card|integer',
            'details.expire_date' => 'required_if:type,credit_card|string',
            'details.first_name' => 'required_if:type,cash_on_delivery|string',
            'details.last_name' => 'required_if:type,cash_on_delivery|string',
            'details.address' => 'required_if:type,cash_on_delivery|string',
            'details.swift' => 'required_if:type,bank_transfer|string',
            'details.iban' => 'required_if:type,bank_transfer|string',
            'details.name' => 'required_if:type,bank_transfer|string',
        ];
    }
}
