<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewResults', Attempt::class);

        $results = $request->user()
            ->attempts()
            ->where(
                'grading_status',
                'graded',
            )
            ->whereNotNull('score')
            ->whereHas(
                'exam',
                fn ($query) => $query  
                    ->whereNotNull('results_released_at'),
            )
            ->with([
                'exam:id,subject_id,title,results_released_at',
                'exam.subject:id,name,code',
            ])
            ->latest('submitted_at')
            ->paginate(10);
    
        return view('student.results.index', compact('results'));
    }

    public function show(Attempt $attempt): View
    {
        Gate::authorize('viewResult', $attempt);

        $attempt->load([
            'exam.subject:id,name,code',
            'exam.questions:id,exam_id,type,prompt,marks,position',
            'answers:id,attempt_id,question_id,question_option_id,text_answer,awarded_marks',
            'answers.selectedOption:id,text',
        ]);

        $answers = $attempt->answers
            ->keyBy('question_id');

        $totalMarks = (float) $attempt
            ->exam
            ->questions
            ->sum('marks');

        $percentage = $totalMarks > 0
            ? ((float) $attempt->score
                / $totalMarks) * 100
            : 0;

        return view(
            'student.results.show',
            compact(
                'attempt',
                'answers',
                'totalMarks',
                'percentage',
            ),
        );
    }
}