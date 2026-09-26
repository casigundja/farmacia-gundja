<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_type' => ['nullable', 'in:delivery,pickup,DELIVERY,PICKUP'],
            'address_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mcx_phone' => ['nullable', 'string', 'max:30'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'prescription_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'patient_name' => ['nullable', 'string', 'max:150'],
            'doctor_name' => ['nullable', 'string', 'max:150'],
            'doctor_reg_number' => ['nullable', 'string', 'max:50'],
            'doctor_crm' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_type.required' => 'Selecione a modalidade de entrega (ao domicílio ou levantamento na loja).',
            'address_id.required_if' => 'Selecione um endereço para entrega ao domicílio.',
            'branch_id.required_if' => 'Selecione a farmácia onde deseja levantar o seu pedido.',
            'payment_method.required' => 'Selecione a forma de pagamento desejada.',
        ];
    }
}
