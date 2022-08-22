<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorepaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:payments,name'],
            'type' => ['required', 'numeric']
        ];
    }
}
