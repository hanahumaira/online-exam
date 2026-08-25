<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Grade Attempt') }}
        </h2>
    </x-slot>

    <div class="py-12">
       <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                     <dl class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Student
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ $attempt->student->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Exam
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ $attempt->exam->title }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Grading
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ ucfirst(str_replace(
                                    '_',
                                    ' ',
                                    $attempt->grading_status,
                                )) }}
                            </dd>
                        </div>
                    </dl>

                    @if ($attempt->grading_status === 'graded')
                        <p class="mt-4 font-medium text-green-700">
                            Final score:
                            {{ number_format(
                                (float) $attempt->score,
                                2,
                            ) }}
                        </p>
                    @endif
                </div>
            </div>

            <form 
                method="POST"
                action="{{ route('lecturer.grading.update', $attempt) }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                @foreach ($attempt->exam->questions as $question)
                    @php
                        $answer = $answers->get($question->id);
                    @endphp

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-900">
                                Question {{ $question->position }}
                            </h3>

                            <p class="mt-2 text-gray-800">
                                {{ $question->prompt }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Maximum: {{ $question->marks }} marks
                            </p>

                            @if ($question->type === 'multiple_choice')
                                <div class="mt-4 space-y-2">
                                    @foreach ( $question->options as $option)
                                        <p
                                            class="{{ $option->is_correct
                                                ? 'font-medium text-green-700'
                                                : 'text-gray-700' }}"
                                        >
                                            @if (
                                                $answer?->question_option_id
                                                === $option->id
                                            )
                                                Student selected:
                                            @endif

                                            {{ $option->text }}

                                            @if ($option->is_correct)
                                                (Correct)
                                            @endif
                                        </p>
                                    @endforeach
                                </div>
                                <p class="mt-4 font-medium text-gray-900">
                                    Awarded:
                                    {{ $answer?->awarded_marks ?? 0 }}
                                    / {{ $question->marks }}
                                </p>
                            @else
                                <div class="mt-4 rounded-md bg-gray-50 p-4">
                                    @if ($answer?->text_answer)
                                        <p class="whitespace-pre-line text-gray-800">
                                            {{ $answer->text_answer }}
                                        </p>
                                    @else
                                        <p class="text-gray-500">
                                            No answer provided.
                                        </p>
                                    @endif
                                </div>

                                @if (
                                    $attempt->grading_status
                                    === 'awaiting_manual'
                                    && $answer?->awarded_marks === null
                                )
                                    <div class="mt-4">
                                        <label
                                            for="marks_{{ $answer->id }}"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Awarded Marks
                                        </label>

                                        <input
                                            id="marks_{{ $answer->id }}"
                                            type="number"
                                            name="marks[{{ $answer->id }}]"
                                            value="{{ old(
                                                'marks.'.$answer->id,
                                            ) }}"
                                            min="0"
                                            max="{{ $question->marks }}"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >

                                        <x-input-error
                                            :messages="$errors->get(
                                                'text_answers.'.$question->id
                                            )"
                                            class="mt-2"
                                        />
                                    </div>
                                @else
                                    <p class="mt-4 font-medium text-gray-900">
                                        Awarded:
                                        {{ $answer?->awarded_marks ?? 0 }}
                                        / {{ $question->marks }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach

                @if (
                    $attempt->grading_status
                    === 'awaiting_manual'
                )
                    <x-primary-button>
                        {{ __('Save Manual Grading') }}
                    </x-primary-button>
                @endif
            </form>

            <a 
                href="{{ route('lecturer.grading.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to grading
            </a>
        </div>
    </div>
</x-app-layout>