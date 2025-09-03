<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:products,name,NULL,id,deleted_at,NULL',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0|decimal:0,2',
            'stock' => 'required|integer|min:0'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es requerido.',
            'name.unique' => 'Ya existe un producto con este nombre.',
            'description.required' => 'La descripción del producto es requerida.',
            'price.required' => 'El precio del producto es requerido.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',
            'stock.required' => 'El stock del producto es requerido.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Este método fuerza que los errores se devuelvan como JSON en lugar de redirección
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
