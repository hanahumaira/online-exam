<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Available Exams')}}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if ($student->classroom === null)
                 <div class="rounded-md bg-yellow-50 p-4 text-yellow-800">
                    You have not been assigned to a classroom yet.
                    Please contact your lecturer.
                </div>
            @elseif ($exams->isEmpty())
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        There are no published exams available for
                        {{ $student->classroom->name }}.
                    </div>
                </div>
            @else
                <div class="mb-4 text-sm text-gray-600">
                    Classroom:
                    <span class="font-medium text-gray-900">
                        {{ $student->classroom->name }}
                        {{ $student->classroom->code }}
                    </span>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($exams as $exam)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $exam->title }}
                                </h3>

                                <p class="mt-2 text-sm text-gray-600">
                                    Subject:
                                    {{ $exam->subject->name }}
                                    ({{ $exam->subject->code }})
                                </p>

                                <dl class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm text-gray-500">
                                            Duration
                                        </dt>

                                        <dd class="font-medium text-gray-900">
                                            {{ $exam->duration_minutes }}
                                            minutes
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm text-gray-500">
                                            Questions
                                        </dt>

                                        <dd class="font-medium text-gray-900">
                                            {{ $exam->questions_count }}
                                        </dd>
                                    </div>
                                </dl>

                                <div>
                                    <a 
                                        href="{{ route('student.exams.show', $exam) }}"
                                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                                    >
                                        View Exam
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $exams->links()}}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>