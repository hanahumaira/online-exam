<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Exam::class);

        $student = $request->user()->load(
            'classroom:id,name,code',
        );

        $query = Exam::query()
            ->whereNotNull('published_at') //remove draft
            ->whereNull('results_released_at') //hide released
            ->with('subject:id,name,code')
            ->withCount('questions');

        if ($student->classroom_id === null) {
            $query->whereKey(-1);
        } else {
            $query->whereHas( //assigned classroom
                'classrooms',
                fn ($query) => $query->where(
                    'classrooms.id',
                    $student->classroom_id,
                ),
            );
        }

        $exams = $query
            ->orderByDesc('published_at')
            ->paginate(10);

        return view(
            'student.exams.index',
            compact('student', 'exams'),
        );

    }


    public function show(Request $request, Exam $exam): View
    {
        Gate::authorize('view', $exam);

        $exam->load('subject:id,name,code');
        $exam->loadCount('questions');

        $attempt = $exam->attempts()
            ->where('user_id', $request->user()->id)
            ->first();

        return view(
            'student.exams.show',
            compact('exam', 'attempt'),
        );
    }
}
