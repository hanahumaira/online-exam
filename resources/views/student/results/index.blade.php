<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('My Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if ($results->isEmpty())
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        No results have been released yet.
                    </div>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($results as $result)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $result->exam->title }}
                                </h3>

                                <p class="mt-2 text-sm text-gray-600">
                                    {{ $result->exam->subject->name }}
                                    ({{ $result->exam->subject->code }})
                                </p>

                                <p class="mt-4 text-2xl font-semibold text-gray-900">
                                    {{ number_format(
                                        (float) $result->score,
                                        2,
                                    ) }}
                                </p>

                                <a
                                    href="{{ route(
                                        'student.results.show',
                                        $result,
                                    ) }}"
                                    class="mt-6 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                                >
                                    View Result
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $results->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>