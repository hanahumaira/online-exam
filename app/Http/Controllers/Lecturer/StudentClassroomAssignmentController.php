<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentClassroomAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $lecturer = $request->user();

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where(function ($query) use ($lecturer) {
                $query
                    ->whereNull('classroom_id')
                    ->orWhereHas(
                        'classroom',
                        fn ($classroomQuery) => $classroomQuery
                            ->where('created_by', $lecturer->id),
                    );
            })
            ->with('classroom:id,name,code,created_by')
            ->orderBy('name')
            ->paginate(10);

        return view(
            'lecturer.students.index',
            compact('students'),
        );
    }

    public function edit(
        Request $request,
        User $student,
    ): View {
        Gate::authorize('assignClassroom', $student);

        $classrooms = $request->user()
            ->createdClassrooms()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view(
            'lecturer.students.edit-classroom',
            compact('student', 'classrooms'),
        );
    }

    public function update(
        Request $request,
        User $student,
    ): RedirectResponse {
        Gate::authorize('assignClassroom', $student);

        $validated = $request->validate([
            'classroom_id' => [
                'nullable',
                'integer',
                Rule::exists('classrooms', 'id')->where(
                    fn ($query) => $query->where(
                        'created_by',
                        $request->user()->id,
                    ),
                ),
            ],
        ]);

        $student->update([
            'classroom_id' => $validated['classroom_id'] ?? null,
        ]);

        return to_route('lecturer.students.index')
            ->with(
                'success',
                'Student classroom assignment updated successfully.',
            );
    }
}
