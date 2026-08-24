<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $exam->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
       <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
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
                                Number of Questions
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                {{ $exam->questions_count }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Published
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                {{ $exam->published_at->format(
                                    'd M Y, g:i A',
                                ) }}
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

            <div class="rounded-md bg-blue-50 p-4 text-sm text-blue-800">
                Read the instructions carefully before starting.
                Once an attempt is started, the exam timer will begin.
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($attempt === null)
                        <h3 class="text-lg font-semibold text-gray-900">
                            Start Exam
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            You are allowed one attempt. The
                            {{ $exam->duration_minutes }}-minute timer starts
                            immediately after you press the button.
                        </p>

                        <form 
                            method="POST" 
                            action="{{ route('student.exams.attempts.store', $exam) }}"
                            class="mt-6"
                            onsubmit="return confirm(
                                'Start this exam now? The timer will begin immediately.'
                            );"
                        >
                            @csrf

                             <x-primary-button>
                                {{ __('Start Exam') }}
                            </x-primary-button>
                        </form>
                     @elseif ($attempt->status === 'in_progress')
                        <h3 class="text-lg font-semibold text-gray-900">
                            Attempt in Progress
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            You have already started this exam.
                        </p>

                        <a 
                            href="{{ route(
                                'student.attempts.show',
                                $attempt,
                            ) }}"
                            class="mt-6 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                        >
                            Continue Exam
                        </a>
                    @else
                        <h3 class="text-lg font-semibold text-gray-900">
                            Attempt Completed
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            This attempt is {{ str_replace(
                                '_',
                                ' ',
                                $attempt->status,
                            ) }} and cannot be restarted.
                        </p>

                        <a 
                            href="{{ route(
                                'student.attempts.show',
                                $attempt,
                            ) }}"
                            class="mt-6 inline-block text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            View attempt status
                        </a>
                    @endif
                </div>
            </div>
            <a 
                href="{{ route('student.exams.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to available exams
            </a>
        </div>
    </div>
</x-app-layout>