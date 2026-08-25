<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $attempt->exam->title }}
            </h2>

            @if ($attempt->status === 'in_progress')
                <div
                    id="exam-timer"
                    class="rounded-md bg-red-100 px-4 py-2 font-mono text-lg font-semibold text-red-800"
                >
                    00:00
                </div>
            @endif
        </div>
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
                                {{ $attempt->exam->subject->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Started
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ $attempt->started_at->format(
                                    'd M Y, g:i A',
                                ) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Status
                            </dt>

                            <dd class="font-medium text-gray-900">
                                {{ ucfirst(str_replace(
                                    '_',
                                    ' ',
                                    $attempt->status,
                                )) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($attempt->status === 'in_progress')
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    The deadline is enforced by the server.
                    Save your answers regularly.
                </div>

                <form 
                    id="exam-form"
                    method="POST"
                    action="{{ route('student.attempts.update', $attempt) }}"
                    class="space-y-6"
                >
                    @csrf
                    @method('PUT')
                    
                    @foreach ($attempt->exam->questions as $question)
                        @php
                            $savedAnswer = $answers->get(
                                $question->id
                            );

                            $selectedOptionId = old(
                                "option_answers.{$question->id}",
                                $savedAnswer?->question_option_id,
                            );

                            $textAnswer = old(
                                "text_answers.{$question->id}",
                                $savedAnswer?->text_answer,
                            );
                        @endphp

                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <fieldset class="p-6">
                                <legend class="font-semibold text-gray-900">
                                    Question {{ $question->position }}
                                </legend>
                                
                                 <p class="mt-2 text-gray-800">
                                    {{ $question->prompt }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $question->marks }} marks
                                </p>

                                @if ($question->type === 'multiple_choice')
                                    <div class="mt-4 space-y-3">
                                        @foreach ($question->options as $option)
                                            <label class="flex items-start gap-3">
                                                <input 
                                                    type="radio"
                                                    name="option_answers[{{ $question->id }}]"
                                                    value="{{ $option->id }}"
                                                    class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    @checked(
                                                        (string) $selectedOptionId
                                                        === (string) $option->id
                                                    )
                                                >
                                                <span class="text-gray-700">
                                                    {{ $option->text }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <x-input-error 
                                        :messages="$errors->get(
                                            'option_answers.'.$question->id
                                        )"
                                        class="mt-2"
                                    />
                                @else
                                    <textarea
                                        name="text_answers[{{ $question->id }}]"
                                        rows="6"
                                        class="mt-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >{{ $textAnswer }}</textarea>

                                    <x-input-error 
                                        :messages="$errors->get(
                                            'text_answers.'.$question->id
                                        )"
                                        class="mt-2"
                                    />

                                @endif
                            </fieldset>
                        </div>
                    @endforeach

                    <x-input-error 
                        :messages="$errors->get('action')"
                    />

                    <div class="flex flex-wrap items-center gap-4">
                        <button
                            type="submit"
                            name="action"
                            value="save"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Save Progress
                        </button>

                        <button
                            id="submit-exam-button"
                            type="submit"
                            name="action"
                            value="submit"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                            onclick="return confirm(
                                'Submit this exam now? You will not be able to change your answers afterward.'
                            );"
                        >
                            Submit Exam
                        </button>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        let remainingSeconds = @json($remainingSeconds);

                        const timerElement =
                            document.getElementById('exam-timer');

                        const submitButton =
                            document.getElementById(
                                'submit-exam-button',
                            );

                        function displayTime() {
                            const minutes = Math.floor(
                                remainingSeconds / 60,
                            );

                            const seconds =
                                remainingSeconds % 60;

                            timerElement.textContent =
                                String(minutes).padStart(2, '0')
                                + ':'
                                + String(seconds).padStart(2, '0');
                        }

                        displayTime();

                        const timer = setInterval(function () {
                            remainingSeconds--;

                            displayTime();

                            if (remainingSeconds <= 1) {
                                clearInterval(timer);

                                submitButton.removeAttribute('onclick');
                                submitButton.click();
                            }
                        }, 1000);
                    });
                </script>
            @else
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        @if ($attempt->status === 'submitted')
                            <h3 class="text-lg font-semibold text-green-700">
                                Exam Submitted
                            </h3>

                            <p class="mt-2 text-gray-600">
                                Your answers were submitted at
                                {{ $attempt->submitted_at->format(
                                    'd M Y, g:i A',
                                ) }}.
                            </p>
                        @else
                            <h3 class="text-lg font-semibold text-red-700">
                                Time Expired
                            </h3>

                            <p class="mt-2 text-gray-600">
                                This attempt reached its server-enforced
                                deadline and is now closed.
                            </p>
                        @endif
                            <p class="mt-4 text-sm text-gray-600">
                                Results will be available only after grading
                                and lecturer release.
                            </p>

                            @if (
                                $attempt->grading_status
                                === 'awaiting_manual'
                            )
                                <p class="mt-4 text-sm text-yellow-700">
                                    Your attempt is awaiting lecturer grading.
                                </p>
                            @elseif (
                                $attempt->grading_status === 'graded'
                            )
                                <p class="mt-4 text-sm text-green-700">
                                    Grading is complete. Your score will become
                                    visible only after the lecturer releases
                                    the results.
                                </p>
                            @endif
                    </div>
                </div>
            @endif

            <a 
                href="{{ route('student.exams.index') }}"
                class="inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                Back to available exams
            </a>
        </div>
    </div>
</x-app-layout>