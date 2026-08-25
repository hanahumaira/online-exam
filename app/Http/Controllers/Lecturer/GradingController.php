<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;


class GradingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Attempt::class);

        $attempts = Attempt::query()
            ->whereHas(
                'exam',
                fn ($query) =>$query->where(
                    'created_by',
                    $request->user()->id
                ),
            )
            ->whereIn(
                'status',
                ['submitted', 'expired'],
            )
            ->with([
                'student:id,name,email',
                'exam:id,title,created_by',
            ])
            ->latest('submitted_at')
            ->paginate(10);

        return view(
            'lecturer.grading.index',
            compact('attempts'),
        );
    }

    public function show(Attempt $attempt): View
    {
        Gate::authorize('grade', $attempt);

        $attempt->load([
            'student:id,name,email',
            'exam.subject:id,name,code',
            'exam.questions.options:id,question_id,text,is_correct',
            'answers:id,attempt_id,question_id,question_option_id,text_answer,awarded_marks',
        ]);

        $answers = $attempt->answers
            ->keyBy('question_id');

        return view(
            'lecturer.grading.show',
            compact('attempt', 'answers'),
        );
    }

    public function update(Request $request, Attempt $attempt): RedirectResponse
    {
        Gate::authorize('grade', $attempt);

        if ($attempt->grading_status !== 'awaiting_manual') {
            return to_route('lecturer.grading.show', $attempt)
                ->with('error', 'This attempt does not require manual grading.');
        }

        $attempt->load(
            'answers.question:id,type,marks',
        );

        $answersToGrade = $attempt->answers
            ->filter(
                fn ($answer) => 
                    $answer->question->type === 'open_text'
                    && $answer->awarded_marks === null,
            );

        $rules = [
            'marks' => ['required', 'array'],
        ];

        foreach ($answersToGrade as $answer) {
            $rules["marks.{$answer->id}"] = [
                'required',
                'numeric',
                'min:0',
                'max:'.(float) $answer->question->marks,
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use (
            $attempt,
            $answersToGrade,
            $validated,
        ) {
            foreach ($answersToGrade as $answer) {
                $answer->update([
                    'awarded_marks' => $validated['marks'][$answer->id],
                ]);
            }

            $stillRequiresGrading = $attempt
                ->answers()
                ->whereNull('awarded_marks')
                ->exists();

            if (! $stillRequiresGrading) {
                $attempt->update([
                    'grading_status' => 'graded',
                    'score' => $attempt
                        ->answers()
                        ->sum('awarded_marks'),
                ]);
            }
        });

        return to_route('lecturer.grading.show',$attempt)
            ->with('success','Manual grading saved successfully.');
    }
}