<?php

namespace App\Http\Requests;

use App\Models\Produit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAnnonceRequest extends FormRequest
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
            'produit' => ['sometimes', 'string', Rule::exists('produits', 'slug')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'produit.exists' => 'Le produit demandé n\'existe pas.',
            'produit.string' => 'Le produit doit être une chaîne de caractères.',
        ];
    }
}
