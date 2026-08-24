<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExamClassroomAssignmentController extends Controller
{
    public function edit(Request $request, Exam $exam): View
    {
        Gate::authorize('assignClassrooms', $exam);

        $exam->load([
            'subject:id,name',
            'classrooms:id',
        ]);

        $classrooms = $request->user()
            ->createdClassrooms()
            ->whereHas(
                'subjects',
                fn ($query) => $query->where(
                    'subjects.id',
                    $exam->subject_id,
                ),
            )
            ->orderBy('name')
            ->get(['classrooms.id', 'name', 'code']);

        return view(
            'lecturer.exams.classrooms.edit',
            compact('exam', 'classrooms'),
        );
    }

    public function update(
        Request $request,
        Exam $exam,
    ): RedirectResponse {
        Gate::authorize('assignClassrooms', $exam);

        $eligibleClassroomIds = $request->user()
            ->createdClassrooms()
            ->whereHas(
                'subjects',
                fn ($query) => $query->where(
                    'subjects.id',
                    $exam->subject_id,
                ),
            )
            ->pluck('classrooms.id');

        $validated = $request->validate([
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => [
                'integer',
                'distinct',
                Rule::in($eligibleClassroomIds->all()),
            ],
        ]);

        $exam->classrooms()->sync(
            $validated['classroom_ids'] ?? [],
        );

        return to_route('lecturer.exams.show', $exam)
            ->with(
                'success',
                'Exam classroom assignments updated successfully.',
            );
    }
}