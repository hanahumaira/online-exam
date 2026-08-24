<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Exam $exam): bool
    {
        return $user->isLecturer()
            && $exam->created_by === $user->id
            && $exam->published_at === null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->isLecturer()
            && $question->exam->created_by === $user->id
            && $question->exam->published_at === null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->isLecturer()
            && $question->exam->created_by === $user->id
            && $question->exam->published_at === null;
    }
}
