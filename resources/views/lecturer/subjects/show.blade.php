<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $subject->name }}
            </h2>

            <a 
                href="{{ route('lecturer.subjects.edit', $subject) }}"
                class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
            >
                {{__('Edit Subject')}}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
       <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Subject Name
                            </dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $subject->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Subject Code
                            </dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $subject->code }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Assigned Classrooms
                    </h3>

                    @if ($subject->classrooms->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            This subject is not assigned to any classroom.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200">
                            @foreach ($subject->classrooms as $classroom)
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
                    <h3 class="text-lg font-semibold text-gray-900">
                        Exams
                    </h3>
                    @if ($subject->exams->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            No exams have been created for this subject.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200">
                            @foreach ($subject->exams as $exam)
                                <li class="py-3">
                                    <span class="font-medium text-gray-900">
                                        {{ $exam->title }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <a 
                href="{{ route('lecturer.subjects.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to subjects
            </a>
        </div>
    </div>
</x-app-layout>