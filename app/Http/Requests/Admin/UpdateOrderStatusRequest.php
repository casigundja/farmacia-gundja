<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isAttendant() || auth()->user()->isStockist());
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,confirmed,processing,ready,out_for_delivery,delivered,cancelled,PENDING,CONFIRMED,SEPARATING,READY,OUT_FOR_DELIVERY,DELIVERED,CANCELLED'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
