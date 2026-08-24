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

            <a 
                href="{{ route('lecturer.exams.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to exams
            </a>
        </div>
    </div>
</x-app-layout>