<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isLecturer();
    }

    public function assignClassroom(User $lecturer, User $student): bool
    {
        if (! $lecturer->isLecturer()) {
            return false;
        }

        if (! $student->isStudent()) {
            return false;
        }

        if ($student->classroom_id === null) {
            return true;
        }

        return $student->classroom?->created_by === $lecturer->id;
    }
}
