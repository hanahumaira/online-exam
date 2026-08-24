<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(Exam $exam): View
    {
        Gate::authorize(
            'create',
            [Question::class, $exam],
        );

        return view(
            'lecturer.questions.create',
            compact('exam'),
        );

    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        Gate::authorize(
            'create',
            [Question::class, $exam],
        );

        $isMultipleChoice = $request->input('type') === 'multiple_choice';

        $options = array_map(
            fn ($option) => trim((string) $option),
            $request->input('options', []),
        );

        $request->merge([
            'prompt' => trim(
                (string) $request->input('prompt'),
            ),
            'options' => $options,
        ]);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['open_text', 'multiple_choice'])],
            'prompt' => ['required', 'string', 'max:5000'],
            'marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'options' => [Rule::requiredIf($isMultipleChoice), 'array', 'nullable', 'size:4'],
            'options.0' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.1' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.2' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.3' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'correct_option' => [Rule::requiredIf($isMultipleChoice), 'nullable', 'integer', 'between:0,3'],
        ]);

        if (
            $isMultipleChoice
            && blank(
                $validated['options'][
                    $validated['correct_option']
                ] ?? null,
            )
        ) {
            throw ValidationException::withMessages([
                'correct_option' => [
                    'The correct answer must have option text.',
                ],
            ]);
        }

        $question = DB::transaction(
            function () use (
                $exam,
                $validated,
                $isMultipleChoice,
            ) {
                $nextPosition = (
                    $exam->questions()->max('position') ?? 0
                ) + 1;
                $question = $exam->questions()->create([
                    'type' => $validated['type'],
                    'prompt' => $validated['prompt'],
                    'marks' => $validated['marks'],
                    'position' => $nextPosition,
                ]);

                if ($isMultipleChoice) {
                    foreach ($validated['options'] as $index => $text) {
                        if (blank($text)) {
                            continue;
                        }

                        $question->options()->create([
                            'text' => $text,
                            'is_correct' => $index === $validated['correct_option'],
                        ]);
                    }
                }

                return $question;
            },
        );

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Question created successfully.');
    }

    public function edit(Exam $exam, Question $question): View
    {
        $this->ensureQuestionBelongsToExam($exam, $question);

        Gate::authorize('update', $question);

        $question->load('options');

        return view(
            'lecturer.questions.edit',
            compact('exam', 'question'),
        );
    }

    public function update(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        $this->ensureQuestionBelongsToExam($exam, $question);

        Gate::authorize('update', $question);

        $isMultipleChoice = $request->input('type') === 'multiple_choice';

        $options = array_map(
            fn ($option) => trim((string) $option),
            $request->input('options', []),
        );

        $request->merge([
            'prompt' => trim(
                (string) $request->input('prompt'),
            ),
            'options' => $options,
        ]);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['open_text', 'multiple_choice'])],
            'prompt' => ['required', 'string', 'max:5000'],
            'marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'options' => [Rule::requiredIf($isMultipleChoice), 'array', 'nullable', 'size:4'],
            'options.0' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.1' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.2' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'options.3' => [Rule::requiredIf($isMultipleChoice), 'string', 'max:1000'],
            'correct_option' => [Rule::requiredIf($isMultipleChoice), 'nullable', 'integer', 'between:0,3'],
        ]);

        if (
            $isMultipleChoice
            && blank(
                $validated['options'][
                    $validated['correct_option']
                ] ?? null,
            )
        ) {
            throw ValidationException::withMessages([
                'correct_option' => [
                    'The correct answer must have option text.',
                ],
            ]);
        }

        DB::transaction(
            function () use (
                $question,
                $validated,
                $isMultipleChoice,
            ) {
                $question->update([
                    'type' => $validated['type'],
                    'prompt' => $validated['prompt'],
                    'marks' => $validated['marks'],
                ]);

                $question->options()->delete();

                if ($isMultipleChoice) {
                    foreach (
                        $validated['options'] as $index => $text
                    ) {
                        if (blank($text)) {
                            continue;
                        }

                        $question->options()->create([
                            'text' => $text,
                            'is_correct' => $index
                                === $validated['correct_option'],
                        ]);
                    }
                }
            },
        );

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Question updated successfully.');

    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        $this->ensureQuestionBelongsToExam($exam, $question);

        Gate::authorize('delete', $question);

        $question->delete();

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Question deleted successfully.');
    }

    private function ensureQuestionBelongsToExam(Exam $exam, Question $question): void
    {
        abort_unless(
            $question->exam_id === $exam->id,
            404,
        );
    }
}
