<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'date' => ['required', 'date'],
            'customer_name' => ['required'],
            'customer_phone' => ['required'],
            'customer_address' => ['required'],
            'discount' => ['numeric'],
            'deposit' => ['numeric'],
            'items' => ['required', 'array'],
            'items.*' => ['required', 'array'],
            'items.*.name' => ['required'],
            'items.*.price' => ['required', 'numeric'],
            'items.*.quantity' => ['required', 'numeric'],
            'note' => ['string']
        ];
    }
}
