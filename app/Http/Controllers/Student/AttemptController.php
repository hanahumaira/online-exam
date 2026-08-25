<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Exam;
use App\Services\AttemptGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttemptController extends Controller
{
    public function store(Request $request, Exam $exam, AttemptGradingService $gradingService): RedirectResponse
    {
        Gate::authorize('view', $exam);

        $startedAt = now();

        $attempt = $request->user()
            ->attempts()
            ->firstOrCreate(
                [
                    'exam_id' => $exam->id,
                ],
                [
                    'started_at' => $startedAt,
                    'expires_at' => $startedAt
                        ->copy()
                        ->addMinutes($exam->duration_minutes),
                    'status' => 'in_progress',
                ],
            );

        if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
            $gradingService->finalizeAttempt($attempt, 'expired');
        }

        return to_route(
            'student.attempts.show',
            $attempt,
        );
    }

    public function show(Attempt $attempt, AttemptGradingService $gradingService): View
    {
        Gate::authorize('view', $attempt);

        if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
            $gradingService->finalizeAttempt(
                $attempt,
                'expired',
            );

            $attempt->refresh();
        }

        $attempt->load([
            'exam.subject:id,name,code',
            'exam.questions.options:id,question_id,text',
            'answers:id,attempt_id,question_id,question_option_id,text_answer',
        ]);

        $answers = $attempt->answers->keyBy('question_id');

        $remainingSeconds = max(
            0,
            $attempt->expires_at->getTimestamp() - now()->getTimestamp(),
        );

        return view(
            'student.attempts.show',
            compact('attempt', 'answers','remainingSeconds'),
        );
    }

    public function update(Request $request, Attempt $attempt, AttemptGradingService $gradingService): RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($attempt->status !=='in_progress') {
            return to_route(
                'student.attempts.show',
                $attempt,
            )->with(
                'error',
                'This attempt can no longer be modified.',
            );
        }

        if (now()->gte($attempt->expires_at)) {
            $gradingService->finalizeAttempt(
                $attempt,
                'expired',
            );

            return to_route(
                'student.attempts.show',
                $attempt,
            )->with(
                'error',
                'The exam time has expired.'
            );
        }

        Gate::authorize('update', $attempt);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['save', 'submit'])],
            'option_answers' => ['nullable', 'array'],
            'option_answers.*' => ['nullable', 'integer'],
            'text_answers' => ['nullable', 'array'],
            'text_answers.*' => ['nullable', 'string', 'max:10000'],
        ]);

        $attempt->load(
            'exam.questions.options:id,question_id',
        );

        DB::transaction(function () use (
            $attempt,
            $validated,
        ) {
            foreach($attempt->exam->questions as $question) {
                if ($question->type === 'multiple_choice') {
                    $selectedOptionId = $validated['option_answers'][$question->id] ?? null;

                    if ($selectedOptionId === null) {
                        $attempt->answers()
                            ->where(
                                'question_id',
                                $question->id,
                            )
                            ->delete();

                        continue;
                    }

                    $optionBelongsToQuestion = $question->options->contains('id', $selectedOptionId,);

                    if (! $optionBelongsToQuestion) {
                        throw ValidationException::withMessages([
                            "option_answers.{$question->id}" =>
                                'The selected option is invalid.',
                        ]);
                    }
                    
                    $attempt->answers()->updateOrCreate(
                        [
                            'question_id' => $question->id,
                        ],
                        [
                            'question_option_id' => $selectedOptionId,
                            'text_answer' => null,
                            'awarded_marks' => null,
                        ],
                    );

                    continue;
                }

                $textAnswer = trim(
                    (string) (
                        $validated['text_answers'][$question->id] ?? ''
                    ),
                );

                if ($textAnswer === '') {
                    $attempt->answers()
                        ->where(
                            'question_id',
                            $question->id,
                        )
                        ->delete();

                    continue;
                }

                $attempt->answers()->updateOrCreate(
                    [
                        'question_id' => $question->id,
                    ],
                    [
                        'question_option_id' => null,
                        'text_answer' => $textAnswer,
                        'awarded_marks' => null,
                    ],
                );
            }
        });

        if($validated['action'] === 'submit') {
            $gradingService->finalizeAttempt(
                $attempt,
                'submitted',
            );

            return to_route('student.attempts.show', $attempt)
                ->with('success', 'Exam submitted successfully.');
        }

        return to_route('student.attempts.show', $attempt)
            ->with('success','Answers saved successfully.');

    }
}
