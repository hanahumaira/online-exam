<?php

namespace App\Services;

use App\Models\Attempt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttemptGradingService
{
    public function finalizeAttempt(Attempt $attempt, string $status): void
    {
        if (! in_array($status, ['submitted', 'expired'], true)) {
            throw new InvalidArgumentException('Invalid status.');
        }

        DB::transaction(function () use (
            $attempt,
            $status
        ){
            $lockedAttempt = Attempt::query()
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($lockedAttempt->status !== 'in_progress') {
                return;
            }

            $lockedAttempt->load([
                'exam.questions.options:id,question_id,is_correct',
            ]);

            foreach($lockedAttempt->exam->questions as $question) {
                $answer = $lockedAttempt
                    ->answers()
                    ->firstOrCreate(
                        [
                            'question_id' => $question->id,
                        ],
                        [
                            'question_option_id' => null,
                            'text_answer' => null,
                            'awarded_marks' => null,
                        ],
                    );

                if ($question->type === 'multiple_choice') {
                    $selectedOption = $question->options
                        ->firstWhere('id', $answer->question_option_id);

                    $awardedMarks = $selectedOption?->is_correct
                        ?(float) $question->marks
                        : 0;

                    $answer->update([
                            'text_answer' => null,
                            'awarded_marks' => $awardedMarks,
                        ]);

                    continue;
                }

                $hasTextAnswer = trim(
                    (string) $answer->text_answer,
                ) !== '';

                $answer->update([
                    'question_option_id' => null,
                    'awarded_marks' => $hasTextAnswer
                        ? null
                        : 0,
                ]);
            }

            $requiresManualGrading = $lockedAttempt
                ->answers()
                ->whereHas(
                    'question',
                    fn ($query) => $query->where(
                        'type',
                        'open_text',
                    ),
                )
                ->whereNull('awarded_marks')
                ->exists();

            $score = $requiresManualGrading
                ? null
                : $lockedAttempt
                    ->answers()
                    ->sum('awarded_marks');

            $lockedAttempt->update([
                'status' => $status,
                'submitted_at' => $status === 'expired'
                    ? $lockedAttempt->expires_at
                    : now(),
                'grading_status' => $requiresManualGrading
                    ? 'awaiting_manual'
                    : 'graded',
                'score' => $score,
            ]);
        });
    }
}