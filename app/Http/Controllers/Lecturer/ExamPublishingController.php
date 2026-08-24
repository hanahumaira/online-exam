<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExamPublishingController extends Controller
{
    public function store(
        Request $request,
        Exam $exam,
    ): RedirectResponse {
        Gate::authorize('publish', $exam);

        $exam->load([
            'questions.options',
            'classrooms.subjects',
        ]);

        if ($exam->questions->isEmpty()) {
            return to_route('lecturer.exams.show', $exam)
                ->with(
                    'error',
                    'Add at least one question before publishing.',
                );
        }

        if ($exam->classrooms->isEmpty()) {
            return to_route('lecturer.exams.show', $exam)
                ->with(
                    'error',
                    'Assign at least one classroom before publishing.',
                );
        }

        $hasInvalidClassroom = $exam->classrooms
            ->contains(function ($classroom) use ($request, $exam) {
                $belongsToLecturer =
                    $classroom->created_by === $request->user()->id;

                $hasExamSubject = $classroom->subjects
                    ->contains('id', $exam->subject_id);

                return ! $belongsToLecturer || ! $hasExamSubject;
            });

        if ($hasInvalidClassroom) {
            return to_route('lecturer.exams.show', $exam)
                ->with(
                    'error',
                    'One or more assigned classrooms are not eligible for this exam.',
                );
        }

        $hasInvalidQuestion = $exam->questions
            ->contains(function ($question) {
                if (! in_array(
                    $question->type,
                    ['multiple_choice', 'open_text'],
                    true,
                )) {
                    return true;
                }

                if ((float) $question->marks <= 0) {
                    return true;
                }

                if ($question->type === 'open_text') {
                    return false;
                }

                $optionCount = $question->options->count();

                $correctOptionCount = $question->options
                    ->where('is_correct', true)
                    ->count();

                return $optionCount < 2
                    || $correctOptionCount !== 1;
            });

        if ($hasInvalidQuestion) {
            return to_route('lecturer.exams.show', $exam)
                ->with(
                    'error',
                    'Every multiple-choice question must have at least two options and exactly one correct answer.',
                );
        }

        $exam->update([
            'published_at' => now(),
        ]);

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Exam published successfully.');
    }
}