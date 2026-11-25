<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\User;

class AnnoncePolicy
{
    /**
     * Determine whether the user can update the announcement.
     */
    public function update(User $user, Annonce $annonce): bool
    {
        return $user->id === $annonce->user_id;
    }

    /**
     * Determine whether the user can delete the announcement.
     */
    public function delete(User $user, Annonce $annonce): bool
    {
        return $user->id === $annonce->user_id;
    }
}
