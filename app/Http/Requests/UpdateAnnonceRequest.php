<?php

namespace App\Http\Requests;

use App\Models\Annonce;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnonceRequest extends FormRequest
{
    use AnnonceRequestRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $annonce = $this->route('annonce');

        return $annonce instanceof Annonce
            && $this->user()->can('update', $annonce);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->annonceRules();
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->annonceMessages();
    }
}
