<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('products.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'internal_code' => ['required', 'string', 'max:255', Rule::unique('products', 'internal_code')->ignore($this->route('product'))],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'product_type' => ['required', 'in:MEDICAMENTO,HIGIENE,COSMETICO,PERFUMARIA,SUPLEMENTO,BEBE,OUTRO'],
            'requires_prescription' => ['sometimes', 'boolean'],
            'controlled' => ['sometimes', 'boolean'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=5000,max_height=5000'],
            'set_primary' => ['sometimes', 'boolean'],
        ];
    }
}
