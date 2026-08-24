<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Add Question')}}
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
                <div class="p-6 text-gray-900">
                    @include(
                        'lecturer.questions.partials.form',
                        [
                            'exam' => $exam,
                            'question' => null,
                        ]
                    )
                </div>
            </div>
        </div>
    </div>
</x-app-layout>