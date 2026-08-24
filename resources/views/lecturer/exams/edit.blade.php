<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Exam')}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include(
                        'lecturer.exams.partials.form',
                        [
                            'exam' => $exam,
                            'subjects' => $subjects,
                        ]
                    )
                </div>
            </div>
        </div>
    </div>
</x-app-layout>