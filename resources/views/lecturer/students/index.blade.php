<x-app-layout>
    <x-slot name="header">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Student Assignment') }}
            </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
            <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                {{ session('success')}}
            </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                @if ($students->isEmpty())
                    <div class="p-6 text-gray-600">
                        No students are available for assignment.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Student
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Classrooms
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($students as $student)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ $student->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ $student->email }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            @if ($student->classroom)
                                                {{ $student->classroom->name }}
                                                ({{ $student->classroom->code }})
                                            @else
                                                <span class="text-gray-500">
                                                    Unassigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <a
                                                href="{{ route('lecturer.students.classroom.edit', $student) }}"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ $student->classroom_id
                                                    ? 'Change Class'
                                                    : 'Assign Class' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>