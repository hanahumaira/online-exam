@php
    $exam = $exam ?? null;
    $isEditing = $exam !== null;
@endphp

<form
    method="POST"
    action="{{ $isEditing
        ? route('lecturer.exams.update', $exam)
        : route('lecturer.exams.store') }}"
>
    @csrf

    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="subject_id" :value="__('Subject')" />

        <select 
            name="subject_id" 
            id="subject_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">
                Select a subject
            </option>

            @foreach ($subjects as $subject)
                <option 
                    value="{{ $subject->id }}"
                    @selected(
                        (string) old(
                            'subject_id',
                            $exam?->subject_id,
                        ) === (string) $subject->id
                    )
                >
                    {{ $subject->name }}
                    {{ $subject->code }}
                </option>
            @endforeach
        </select>

        <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />

        @if ($subjects->isEmpty())
            <p class="mt-2 text-sm text-gray-600">
                You must create a subject before creating an exam.
            </p>

            <a 
                href="{{ route('lecturer.subjects.create')}}"
                class="mt-1 inline-block text-sm text-indigo-600 underline"
            >
                Create a subject
            </a>
        @endif
    </div>

    <div class="mt-4">
        <x-input-label for="title" :value="__('Exam Title')" />

        <x-text-input
            id="title"
            class="mt-1 block w-full"
            type="text"
            name="title"
            :value="old('title', $exam?->title)"
            required
        />

        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="instructions" :value="__('Instructions')" />

        <textarea
            id="instructions"
            name="instructions"
            rows="5"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            {{ old('instructions', $exam?->instructions) }}
        </textarea>

        <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
    </div>

     <div class="mt-4">
        <x-input-label for="duration_minutes" :value="__('Duration in Minutes')" />

        <x-text-input
            id="duration_minutes"
            class="mt-1 block w-full"
            type="number"
            name="duration_minutes"
            :value="old('duration_minutes', $exam?->duration_minutes)"
            min="1"
            max="480"
            required
        />

        <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
    </div>

    <p class="mt-4 text-sm text-gray-600">
        The exam will be saved as a draft.
    </p>

    <div class="mt-6 flex items-center gap-4">
        <x-primary-button>
            {{ $isEditing ? __('Update Exam') : __('Create Exam') }}
        </x-primary-button>

        <a
            href="{{ route('lecturer.exams.index') }}"
            class="text-sm text-gray-600 hover:text-gray-900"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</form>