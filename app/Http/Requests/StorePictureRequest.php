<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePictureRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:1,2'],
            'picture' => ['required', 'image']
        ];
    }
}
