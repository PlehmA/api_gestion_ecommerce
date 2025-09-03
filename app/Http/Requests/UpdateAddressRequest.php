<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateAddressRequest extends FormRequest
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
            'street' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'zip' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:delivery,billing'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'street.required' => 'La dirección es requerida.',
            'city.required' => 'La ciudad es requerida.',
            'state.required' => 'El estado/provincia es requerido.',
            'zip.required' => 'El código postal es requerido.',
            'country.required' => 'El país es requerido.',
            'type.required' => 'El tipo de dirección es requerido.',
            'type.in' => 'El tipo de dirección debe ser "delivery" o "billing".'
        ];
    }

    /**
     * Handle a failed validation attempt.
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
