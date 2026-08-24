<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isLecturer() || $user->isStudent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Exam $exam): bool
    {
        return $this->ownsExam($user, $exam) || $this->isEligibleStudent($user, $exam);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isLecturer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Exam $exam): bool
    {
        return $this->ownsExam($user, $exam) && $exam->published_at === null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Exam $exam): bool
    {
        return $this->ownsExam($user, $exam) && $exam->published_at === null;
    }

    public function assignClassrooms(User $user, Exam $exam,): bool
    {
        return $this->ownsExam($user, $exam) && $exam->published_at === null;
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $this->ownsExam($user, $exam) && $exam->published_at === null;
    }

    private function ownsExam(User $user, Exam $exam): bool
    {
        return $user->isLecturer() && $exam->created_by === $user->id;
    }

    private function isEligibleStudent(User $user, Exam $exam): bool
    {
        if (! $user->isStudent() || $user->classroom_id === null || $exam->published_at === null) {
            return false;
        }

        return $exam->classrooms()
            ->where('classrooms.id', $user->classroom_id)
            ->exists();
    }
}
