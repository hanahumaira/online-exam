<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if ($exams->isEmpty())
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        There are no published exams.
                    </div>
                </div>
            @else
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Exam
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Attempts
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Status
                                    </th>

                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">
                                                {{ $exam->title }}
                                            </div>

                                            <div class="text-sm text-gray-500">
                                                {{ $exam->subject->name }}
                                                ({{ $exam->subject->code }})
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $exam->attempts_count }}
                                        </td>

                                        <td class="px-6 py-4">
                                            @if ($exam->results_released_at)
                                                <span class="text-green-700">
                                                    Released
                                                </span>
                                            @elseif (
                                                $exam->in_progress_attempts_count > 0
                                            )
                                                <span class="text-yellow-700">
                                                    Attempts in progress
                                                </span>
                                            @elseif (
                                                $exam->ungraded_attempts_count > 0
                                            )
                                                <span class="text-yellow-700">
                                                    Grading incomplete
                                                </span>
                                            @else
                                                <span class="text-gray-700">
                                                    Ready for review
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <a
                                                href="{{ route(
                                                    'lecturer.results.show',
                                                    $exam,
                                                ) }}"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $exams->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>