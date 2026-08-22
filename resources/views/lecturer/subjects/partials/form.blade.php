@php
    $subject = $subject ?? null;
    $isEditing = $subject !== null;

    $selectedClassrooms = old(
        'classroom_ids',
        $subject
            ? $subject->classrooms->pluck('id')->all()
        : [],
    );
@endphp

<form
    method="POST"
    action="{{ $isEditing
        ? route('lecturer.subjects.update', $subject)
        : route('lecturer.subjects.store') }}"
>
    @csrf

    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="name" :value="__('Subject Name')" />

        <x-text-input
            id="name"
            class="mt-1 block w-full"
            type="text"
            name="name"
            :value="old('name', $subject?->name)"
            required
            autofocus
        />

        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="code" :value="__('Subject Code')" />

        <x-text-input
            id="code"
            class="mt-1 block w-full"
            type="text"
            name="code"
            :value="old('code', $subject?->code)"
            required
        />

        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <fieldset class="mt-6">
        <legend class="text-sm font-medium text-gray-700">
            Assign Classrooms
        </legend>

        @if ($classrooms->isEmpty())
            <p class="mt-2 text-sm text-gray-600">
                No classrooms are available.
                Create a classroom before assigning this subject.
            </p>
        @else
            <div class="mt-3 space-y-2">
                @foreach ($classrooms as $classroom)
                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="classroom_ids[]"
                            value="{{ $classroom->id }}"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(in_array(
                                $classroom->id,
                                $selectedClassrooms,
                            ))                        
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
            {{ $isEditing ? __('Update Subject') : __('Create Subject') }}
        </x-primary-button>

        <a
            href="{{ route('lecturer.subjects.index') }}"
            class="text-sm text-gray-600 hover:text-gray-900"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</form>