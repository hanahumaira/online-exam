<?php

namespace App\Policies;

use App\Models\Attempt;
use App\Models\User;

class AttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLecturer();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attempt $attempt): bool
    {
        return $user->isStudent()
            && $attempt->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attempt $attempt): bool
    {
        return $this->view($user, $attempt)
            && $attempt->status === 'in_progress'
            && now()->lt($attempt->expires_at);
    }

    public function grade(User $user, Attempt $attempt): bool
    {
        if (! $user->isLecturer() || $attempt->status === 'in_progress') {
            return false;
        }

        return $attempt->exam()
            ->where('created_by', $user->id)
            ->exists();
    }
}
