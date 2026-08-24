@php
    $selectedClassroomIds = old(
        'classroom_ids',
        $exam->classrooms->pluck('id')->all(),
    );
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Assign Exam to Classrooms')}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Exam
                </p>

                <p class="font-medium text-gray-900">
                    {{ $exam->title }}
                </p>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form 
                        method="POST"
                        action="{{ route('lecturer.exams.classrooms.update', $exam) }}"
                    >
                        @csrf
                        @method('PUT')

                        <fieldset class="mt-6">
                            <legend class="text-sm font-medium text-gray-700">
                                Eligible Classrooms
                            </legend>

                            <p class="mt-1 text-sm text-gray-600">
                                Only your classrooms that include this exam's subject are shown.
                            </p>

                            @if ($classrooms->isEmpty())
                                <div class="mt-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                                    No eligible classrooms are available.
                                    Assign {{ $exam->subject->name }}
                                    to one of your classrooms first.
                                </div>
                            @else
                                <div class="mt-4 space-y-3">
                                    @foreach ($classrooms as $classroom)
                                        <label class="flex items-center gap-3">
                                            <input
                                                type="checkbox"
                                                name="classroom_ids[]"
                                                value="{{ $classroom->id }}"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                @checked(
                                                    in_array(
                                                        $classroom->id,
                                                        $selectedClassroomIds,
                                                    )
                                                )
                                            >

                                            <span class="text-sm text-gray-700">
                                                {{ $classroom->name }}
                                                ({{ $classroom->code }})
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <x-input-error :messages="$errors->get('classroom_ids')" class="mt-2" />

                            <x-input-error :messages="$errors->get('classroom_ids.*')" class="mt-2" />
                        </fieldset>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Save Assignments') }}
                            </x-primary-button>

                            <a
                                href="{{ route(
                                    'lecturer.exams.show',
                                    $exam,
                                ) }}"
                                class="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>