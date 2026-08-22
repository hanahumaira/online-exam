<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $classroom->name }}
            </h2>

            <a 
                href="{{ route('lecturer.classrooms.edit', $classroom) }}"
                class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
            >
                {{__('Edit Classroom')}}
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
                                Class Name
                            </dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $classroom->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Class Code
                            </dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $classroom->code }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Students
                    </h3>

                    @if ($classroom->students->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            No students are assigned to this classroom.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200">
                            @foreach ($classroom->students as $student)
                                <li class="py-3">
                                    <p class="font-medium text-gray-900">
                                        {{ $student->name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $student->email }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

           <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Subjects
                    </h3>
                    @if ($classroom->subjects->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">
                            No subjects are assigned to this classroom.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200">
                            @foreach ($classroom->subjects as $subject)
                                <li class="flex justify-between py-3">
                                    <span class="font-medium text-gray-900">
                                        {{ $subject->name }}
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        {{ $subject->code }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <a 
                href="{{ route('lecturer.classrooms.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to classrooms
            </a>
        </div>
    </div>
</x-app-layout>