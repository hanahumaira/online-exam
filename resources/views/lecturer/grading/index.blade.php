<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Grading')}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if ($attempts->isEmpty())
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        There are no submitted attempts yet.
                    </div>
                </div>
            @else
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Student
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Exam
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Submission
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Grading
                                    </th>

                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($attempts as $attempt)
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
                                            {{ $attempt->exam->title }}
                                        </td>

                                        <td class="px-6 py-4 text-gray-700">
                                            {{ ucfirst($attempt->status) }}
                                        </td>

                                        <td class="px-6 py-4">
                                            @if( $attempt->grading_status === 'awaiting_manual')
                                                <span class="text-yellow-700">
                                                    Awaiting Manual Grading
                                                </span>
                                            @elseif( $attempt->grading_status === 'graded')
                                                <span class="text-green-700">
                                                    Graded
                                                </span>
                                            @else
                                                <span class="text-gray-600">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <a 
                                                href="{{ route('lecturer.grading.show', $attempt) }}"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $attempts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>