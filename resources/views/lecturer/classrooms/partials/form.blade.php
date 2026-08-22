@php
    $classroom = $classroom ?? null;
    $isEditing = $classroom !== null;
@endphp

<form
    method="POST"
    action="{{ $isEditing
        ? route('lecturer.classrooms.update', $classroom)
        : route('lecturer.classrooms.store') }}"
>
    @csrf

    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="name" :value="__('Class Name')" />

        <x-text-input
            id="name"
            class="mt-1 block w-full"
            type="text"
            name="name"
            :value="old('name', $classroom?->name)"
            required
            autofocus
        />

        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="code" :value="__('Class Code')" />

        <x-text-input
            id="code"
            class="mt-1 block w-full"
            type="text"
            name="code"
            :value="old('code', $classroom?->code)"
            required
        />

        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div class="mt-6 flex items-center gap-4">
        <x-primary-button>
            {{ $isEditing ? __('Update Classroom') : __('Create Classroom') }}
        </x-primary-button>

        <a
            href="{{ route('lecturer.classrooms.index') }}"
            class="text-sm text-gray-600 hover:text-gray-900"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</form>