<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ $exam->title }} — Results
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Subject
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ $exam->subject->name }}
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm text-gray-500">
                                Total Marks
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ number_format(
                                    $totalMarks,
                                    2,
                                ) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Release Status
                            </dt>

                            <dd class="font-medium text-gray-900">
                                @if ($exam->results_released_at)
                                    Released on
                                    {{ $exam->results_released_at->format(
                                        'd M Y, g:i A',
                                    ) }}
                                @else
                                    Not released
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if ($exam->results_released_at === null)
                        <div>
                            @if ($attempts->total() === 0)
                                <p class="text-sm text-yellow-700">
                                    Results cannot be released because
                                    no attempts exist.
                                </p>
                            @elseif ($hasInProgressAttempts)
                                <p class="text-sm text-yellow-700">
                                    Wait until all current attempts are
                                    submitted or expired.
                                </p>
                            @elseif ($hasUngradedAttempts)
                                <p class="text-sm text-yellow-700">
                                    Complete all grading before releasing
                                    the results.
                                </p>
                            @endif

                            @if ($canRelease)
                                <form 
                                    method="POST"
                                    action="{{ route('lecturer.results.release', $exam) }}"
                                    onsubmit="return confirm(
                                        'Release these results? This cannot be undone.'
                                    );"
                                >
                                    @csrf

                                    <x-primary-button>
                                        {{ __('Release Results') }}
                                    </x-primary-button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                    Student
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                    Submission
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                    Grading
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                    Score
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($attempts as $attempt)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $attempt->student->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $attempt->student->email }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ ucfirst($attempt->status) }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ ucfirst(str_replace(
                                            '_',
                                            ' ',
                                            $attempt->grading_status,
                                        )) }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        @if (
                                            $attempt->grading_status
                                            === 'graded'
                                        )
                                            {{ number_format(
                                                (float) $attempt->score,
                                                2,
                                            ) }}
                                            /
                                            {{ number_format(
                                                $totalMarks,
                                                2,
                                            ) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-gray-500"
                                    >
                                        No attempts exist.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $attempts->links() }}
            </div>

            <a
                href="{{ route('lecturer.results.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to results
            </a>
        </div>
    </div>
</x-app-layout>