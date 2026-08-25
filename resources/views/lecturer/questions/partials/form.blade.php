@php
    $question = $question ?? null;
    $isEditing = $question !== null;

    $savedOptions = $question
        ? $question->options->pluck('text')->values()->all()
        : [];

    $optionValues = old('options', $savedOptions);
    $optionValues = array_pad($optionValues, 4, '');

    $correctOption = old('correct_option');

    if ($correctOption === null && $question) {
        $correctOption = $question->options
            ->values()
            ->search(
                fn ($option) => $option->is_correct,
            );

        if ($correctOption === false) {
            $correctOption = null;
        }
    }

    $selectedType = old(
        'type',
        $question?->type ?? 'open_text',
    );
@endphp

<form
    method="POST"
    action="{{ $isEditing
        ? route(
            'lecturer.exams.questions.update',
            [$exam, $question],
        )
        : route(
            'lecturer.exams.questions.store',
            $exam
        ) }}"
    x-data="{ type: @js($selectedType) }"
>
    @csrf

    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="type" :value="__('Question Type')" />

        <select 
            name="type" 
            id="type"
            x-model="type"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="open_text">
                Open Text
            </option>

            <option value="multiple_choice">
                Multiple Choice
            </option>
        </select>

        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="prompt" :value="__('Question')" />

        <textarea
            id="prompt"
            name="prompt"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            {{ old('prompt', $question?->prompt) }}
        </textarea>

        <x-input-error :messages="$errors->get('prompt')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="marks" :value="__('Marks')" />

        <x-text-input
            id="marks"
            class="mt-1 block w-full"
            type="number"
            name="marks"
            step="0.01"
            min="0.01"
            max="100"
            :value="old('marks', $question?->marks)"
            required
        />

        <x-input-error :messages="$errors->get('marks')" class="mt-2" />
    </div>

    <div
        x-show="type === 'multiple_choice'"
        class="mt-6"
    >
        <h3 class="text-sm font-medium text-gray-700">
            Answer Options
        </h3>

        <p class="mt-1 text-sm text-gray-600">
            Enter at least two options and select one correct answer.
        </p>

        <div>
            @for ($index = 0; $index < 4; $index++)
                <div class="rounded-md border border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        <input 
                            type="radio"
                            name="correct_option"
                            value="{{ $index }}"
                            class="mt-3 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            @checked(
                                (string) $correctOption === (string) $index
                            )
                        >

                        <div class="flex-1">
                            <label
                                for="option_{{ $index }}"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Option {{ $index + 1 }}
                            </label>

                            <x-text-input
                                id="option_{{ $index }}"
                                type="text"
                                name="options[]"
                                class="mt-1 block w-full"
                                :value="$optionValues[$index]"
                            />

                            <x-input-error :messages="$errors->get('options.'.$index)" class="mt-2" />
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <x-input-error
            :messages="$errors->get('correct_option')"
            class="mt-2"
        />
    </div>

    <div class="mt-6 flex items-center gap-4">
        <x-primary-button>
            {{ $isEditing ? __('Update Question') : __('Create Question') }}
        </x-primary-button>

        <a
            href="{{ route('lecturer.exams.show', $exam) }}"
            class="text-sm text-gray-600 hover:text-gray-900"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</form>