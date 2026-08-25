<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ $attempt->exam->title }} — Result
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Subject
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ $attempt->exam->subject->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Score
                            </dt>

                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format(
                                    (float) $attempt->score,
                                    2,
                                ) }}
                                /
                                {{ number_format(
                                    $totalMarks,
                                    2,
                                ) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Percentage
                            </dt>

                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format(
                                    $percentage,
                                    2,
                                ) }}%
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @foreach (
                $attempt->exam->questions
                as $question
            )
                @php
                    $answer = $answers->get(
                        $question->id,
                    );
                @endphp

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Question {{ $question->position }}
                        </h3>

                        <p class="mt-2 text-gray-800">
                            {{ $question->prompt }}
                        </p>

                        <div class="mt-4 rounded-md bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-500">
                                Your Answer
                            </p>

                            @if (
                                $question->type
                                === 'multiple_choice'
                            )
                                <p class="mt-1 text-gray-800">
                                    {{ $answer?->selectedOption?->text
                                        ?? 'No answer provided.' }}
                                </p>
                            @else
                                <p class="mt-1 whitespace-pre-line text-gray-800">
                                    {{ $answer?->text_answer
                                        ?: 'No answer provided.' }}
                                </p>
                            @endif
                        </div>

                        <p class="mt-4 font-medium text-gray-900">
                            Awarded:
                            {{ number_format(
                                (float) (
                                    $answer?->awarded_marks
                                    ?? 0
                                ),
                                2,
                            ) }}
                            /
                            {{ number_format(
                                (float) $question->marks,
                                2,
                            ) }}
                        </p>
                    </div>
                </div>
            @endforeach

            <a
                href="{{ route('student.results.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to my results
            </a>
        </div>
    </div>
</x-app-layout>