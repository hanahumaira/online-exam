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
            <a 
                href="{{ route('student.exams.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to available exams
            </a>
        </div>
    </div>
</x-app-layout>