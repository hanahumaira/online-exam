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

            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
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

                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Status
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                @if ($exam->published_at)
                                    Published on 
                                    {{ $exam->published_at->format(
                                        'd M Y, g:i A',
                                    )}}
                                @else
                                    Draft
                                @endif
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
                        <h3 class="text-lg font-semibold text-gray-900">
                            Assigned Classrooms
                        </h3>
    
                        @if ($exam->published_at === null)
                            <a
                                href="{{ route(
                                    'lecturer.exams.classrooms.edit',
                                    $exam,
                                ) }}"
                                class="text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                Manage Assignments
                            </a>
                        @endif
                    </div>

                    @if ($exam->classrooms->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            This exam is not assigned to any classroom.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200">
                            @foreach ($exam->classrooms as $classroom)
                                <li class="flex justify-between py-3">
                                    <span class="font-medium text-gray-900">
                                        {{ $classroom->name }}
                                    </span>

                                    <span class="text-sm text-gray-600">
                                        {{ $classroom->code }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
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
                                <div class="rounded-lg border border-gray-200 p-5">
                                    <div
                                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                                    >
                                        <div class="min-w-0">
                                            <p class="text-sm text-gray-500">
                                                Question {{ $question->position }}
                                                ·
                                                {{ $question->type === 'multiple_choice'
                                                    ? 'Multiple Choice'
                                                    : 'Open Text' }}
                                                ·
                                                {{ number_format(
                                                    (float) $question->marks,
                                                    2,
                                                ) }}
                                                marks
                                            </p>

                                            <p class="mt-2 text-base font-medium text-gray-900">
                                                {{ $question->prompt }}
                                            </p>
                                        </div>

                                        @if ($exam->published_at === null)
                                            <div class="flex shrink-0 items-center gap-3">
                                                <a
                                                    href="{{ route(
                                                        'lecturer.exams.questions.edit',
                                                        [$exam, $question],
                                                    ) }}"
                                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'lecturer.exams.questions.destroy',
                                                        [$exam, $question],
                                                    ) }}"
                                                    onsubmit="return confirm(
                                                        'Delete this question?'
                                                    );"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($question->type === 'multiple_choice')
                                        <div class="mt-5 border-t border-gray-200 pt-4">
                                            <p class="text-sm font-medium text-gray-700">
                                                Answer Options
                                            </p>

                                            <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                                                @foreach ($question->options as $option)
                                                    <li
                                                        class="flex items-center justify-between gap-3 rounded-md border px-4 py-3
                                                            {{ $option->is_correct
                                                                ? 'border-green-300 bg-green-50'
                                                                : 'border-gray-200 bg-gray-50' }}"
                                                    >
                                                        <div class="flex min-w-0 items-center gap-3">
                                                            <span
                                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-sm font-medium text-gray-600 ring-1 ring-gray-200"
                                                            >
                                                                {{ $loop->iteration }}
                                                            </span>

                                                            <span
                                                                class="break-words
                                                                    {{ $option->is_correct
                                                                        ? 'font-medium text-green-800'
                                                                        : 'text-gray-700' }}"
                                                            >
                                                                {{ $option->text }}
                                                            </span>
                                                        </div>

                                                        @if ($option->is_correct)
                                                            <span
                                                                class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800"
                                                            >
                                                                Correct
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Publication
                    </h3>

                    @if ($exam->published_at)
                        <p class="mt-4 text-sm text-green-700">
                            This exam was published on
                            {{ $exam->published_at->format(
                                'd M Y, g:i A',
                            ) }}.
                        </p>

                        <p class="mt-2 text-sm text-gray-600">
                            Published exams cannot be modified.
                        </p>
                    @else
                        <p class="mt-4 text-sm text-gray-600">
                            Before publishing, ensure the exam has at least
                            one question and one assigned classroom.
                        </p>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500">
                                    Questions
                                </dt>

                                <dd class="font-medium text-gray-900">
                                    {{ $exam->questions_count }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-gray-500">
                                    Assigned Classrooms
                                </dt>

                                <dd class="font-medium text-gray-900">
                                    {{ $exam->classrooms_count }}
                                </dd>
                            </div>
                        </dl>

                        <form 
                            method="POST"
                            action="{{ route('lecturer.exams.publish', $exam) }}"
                            class="mt-6"
                            onsubmit="return confirm('Publish this exam? It cannot be edited afterward.');"
                        >
                            @csrf

                            <x-primary-button>
                                {{ __('Publish Exam') }}
                            </x-primary-button>
                        </form>
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