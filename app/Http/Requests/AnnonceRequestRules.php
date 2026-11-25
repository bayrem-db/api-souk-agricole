<?php

namespace App\Http\Requests;

trait AnnonceRequestRules
{
    /**
     * Validation rules shared by store and update.
     *
     * @return array<string, mixed>
     */
    protected function annonceRules(): array
    {
        return [
            'produit_id' => ['required', 'exists:produits,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'quantite_kg' => ['required', 'integer', 'min:1'],
            'date_recolte' => ['required', 'date'],
            'disponible' => ['required', 'boolean'],
            'regions' => ['required', 'array', 'min:1'],
            'regions.*' => ['exists:regions,id'],
        ];
    }

    /**
     * French messages shared by store and update.
     *
     * @return array<string, string>
     */
    protected function annonceMessages(): array
    {
        return [
            'produit_id.required' => 'Le produit est obligatoire.',
            'produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'quantite_kg.required' => 'La quantité est obligatoire.',
            'quantite_kg.integer' => 'La quantité doit être un nombre entier.',
            'quantite_kg.min' => 'La quantité doit être d\'au moins 1 kg.',
            'date_recolte.required' => 'La date de récolte est obligatoire.',
            'date_recolte.date' => 'La date de récolte doit être une date valide.',
            'disponible.required' => 'L\'indicateur de disponibilité est obligatoire.',
            'disponible.boolean' => 'L\'indicateur de disponibilité doit être vrai ou faux.',
            'regions.required' => 'Au moins une région est obligatoire.',
            'regions.array' => 'Les régions doivent être une liste.',
            'regions.min' => 'Au moins une région est obligatoire.',
            'regions.*.exists' => 'Une région sélectionnée n\'existe pas.',
        ];
    }
}
