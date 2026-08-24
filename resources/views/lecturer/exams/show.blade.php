<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $exam->title }}
            </h2>

            @if ($exam->published_at === null)
                <a 
                    href="{{ route('lecturer.exams.edit', $exam) }}"
                    class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                >
                    {{__('Edit Exam')}}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
       <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Subject
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                {{ $exam->subject->name }}
                                {{ $exam->subject->code }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Duration
                            </dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $exam->duration_minutes }} minutes
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Instructions
                    </h3>

                    @if ($exam->instructions)
                        <p class="mt-4 whitespace-pre-line text-gray-700">
                            {{ $exam->instructions }}
                        </p>
                    @else
                        <p class="mt-4 text-sm text-gray-600">
                            No instructions have been provided.
                        </p>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Questions
                            </h3>

                            <p class="mt-1 text-sm text-gray-600">
                                Total marks:
                                {{ number_format(
                                    $exam->questions->sum('marks'),
                                    2,
                                )}}
                            </p>
                        </div>

                        @if ($exam->published_at === null)
                            <a 
                                href="{{ route('lecturer.exams.questions.create', $exam) }}"
                                class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                            >
                                Add Question
                            </a>
                        @endif
                    </div>

                    @if ($exam->questions->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            No questions have been added yet.
                        </p>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($exam->questions as $question)
                                <div class="rounded-md border border-gray-200 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Question {{ $question->position }}
                                                ·
                                                {{ $question->type === 'multiple_choice'
                                                    ? 'Multiple Choice'
                                                    : 'Open Text' }}
                                                ·
                                                {{ $question->marks }} marks
                                            </p>

                                            <p class="mt-2 font-medium text-gray-900">
                                                {{ $question->prompt }}
                                            </p>

                                            @if ($exam->published_at === null)
                                                <div class="flex items-center gap-3">
                                                    <a
                                                        href="{{ route('lecturer.exams.questions.edit', [$exam, $question]) }}"
                                                        class="text-sm text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('lecturer.exams.questions.destroy', [$exam, $question]) }}"
                                                        onsubmit="return confirm('Delete this question?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="text-sm text-red-600 hover:text-red-900"
                                                        >
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>

                                        @if ($question->type === 'multiple_choice')
                                       <ul class="mt-4 space-y-2">
                                @foreach ($question->options as $option)
                                            <li
                                                class="{{ $option->is_correct
                                                    ? 'font-medium text-green-700'
                                                    : 'text-gray-700' }}"
                                            >
                                                {{ $option->text }}

                                                @if ($option->is_correct)
                                                    <span class="text-sm">
                                                        (Correct)
                                                    </span>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <a 
                href="{{ route('lecturer.exams.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to exams
            </a>
        </div>
    </div>
</x-app-layout>