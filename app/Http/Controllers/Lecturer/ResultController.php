<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewResults', Exam::class);

        $exams = $request->user()
            ->createdExams()
            ->whereNotNull('published_at')
            ->with('subject:id,name,code')
            ->withCount([
                'attempts',
                'attempts as in_progress_attempts_count' =>
                    fn ($query) => $query->where(
                        'status',
                        'in_progress',
                    ),
                'attempts as ungraded_attempts_count' =>
                    fn ($query) => $query
                        ->where(
                            'status',
                            '!=',
                            'in_progress',
                        )
                        ->where(
                            'grading_status',
                            '!=',
                            'graded',
                        ),
            ])
            ->latest('published_at')
            ->paginate(10);

        return view(
            'lecturer.results.index',
            compact('exams'),
        );
    }

    public function show(Exam $exam): View
    {
        Gate::authorize('manageResults', $exam);

        $exam->load(
            'subject:id,name,code',
        );

        $attempts = $exam->attempts()
            ->with(
                'student:id,name,email',
            )
            ->latest('submitted_at')
            ->paginate(15);

        $totalMarks = (float) $exam
            ->questions()
            ->sum('marks');

        $hasInProgressAttempts = $exam
            ->attempts()
            ->where(
                'status',
                'in_progress',
            )
            ->exists();

        $hasUngradedAttempts = $exam
            ->attempts()
            ->where(
                'status',
                '!=',
                'in_progress',
            )
            ->where(
                'grading_status',
                '!=',
                'graded',
            )
            ->exists();

        $canRelease =
            $exam->results_released_at === null
            && $attempts->total() > 0
            && ! $hasInProgressAttempts
            && ! $hasUngradedAttempts;

        return view(
            'lecturer.results.show',
            compact(
                'exam',
                'attempts',
                'totalMarks',
                'hasInProgressAttempts',
                'hasUngradedAttempts',
                'canRelease',
            ),
        );
    }

    public function release(Exam $exam): RedirectResponse
    {
        Gate::authorize('releaseResults', $exam);

        if ($exam->attempts()->doesntExist()) {
            return to_route('lecturer.results.show', $exam)
                ->with('error', 'Results cannot be released because no attempts exist.');
        }

        if (
            $exam->attempts()
                ->where('status', 'in_progress')
                ->exists()
        ) {
            return to_route('lecturer.results.show', $exam)
                ->with('error', 'Results cannot be released while an attempt is in progress.');
        }

        if (
            $exam->attempts()
                ->where('grading_status', '!=', 'graded')
                ->exists()
        ) {
            return to_route('lecturer.results.show', $exam)
                ->with('error', 'Complete all grading before releasing results.');
        }

        $exam->update([
            'results_released_at' => now(),
        ]);

        return to_route('lecturer.results.show', $exam)
            ->with('success', 'Results released successfully.');
    }
}
