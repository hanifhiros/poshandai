<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        $customer = $this->route('customer');
        return $customer && $this->user()->can('update', $customer);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
        ];
    }
}
