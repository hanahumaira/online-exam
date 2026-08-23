<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Assign Student to Classroom') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <p class="font-medium text-gray-900">
                            {{ $student->name }}
                        </p>

                        <p class="text-sm text-gray-600">
                            {{ $student->email }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route(
                            'lecturer.students.classroom.update',
                            $student,
                        ) }}"
                    >
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label
                                for="classroom_id"
                                :value="__('Classroom')"
                            />

                            <select
                                id="classroom_id"
                                name="classroom_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">
                                    No classroom
                                </option>

                                @foreach ($classrooms as $classroom)
                                    <option
                                        value="{{ $classroom->id }}"
                                        @selected(
                                            (string) old(
                                                'classroom_id',
                                                $student->classroom_id,
                                            ) === (string) $classroom->id
                                        )
                                    >
                                        {{ $classroom->name }}
                                        ({{ $classroom->code }})
                                    </option>
                                @endforeach
                            </select>

                            <x-input-error
                                :messages="$errors->get('classroom_id')"
                                class="mt-2"
                            />
                        </div>

                        @if ($classrooms->isEmpty())
                            <p class="mt-3 text-sm text-gray-600">
                                You have not created any classrooms yet.
                            </p>
                        @endif

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Save Assignment') }}
                            </x-primary-button>

                            <a
                                href="{{ route('lecturer.students.index') }}"
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