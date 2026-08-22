<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Subject::class);

        $subjects = auth()->user()
            ->createdSubjects()
            ->withCount(['classrooms', 'exams'])
            ->latest()
            ->paginate(10);

        return view(
            'lecturer.subjects.index',
            compact('subjects'),
        );
    }

    public function create(): View
    {
        Gate::authorize('create', Subject::class);

        $classrooms = auth()->user()
            ->createdClassrooms()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view(
            'lecturer.subjects.create',
            compact('classrooms'),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Subject::class);

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                'unique:subjects,code',
            ],
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('classrooms', 'id')->where(
                    fn ($query) => $query->where(
                        'created_by',
                        $request->user()->id,
                    ),
                ),
            ],
        ]);

        $classroomIds = $validated['classroom_ids'] ?? [];

        unset($validated['classroom_ids']);

        $subject = $request->user()
            ->createdSubjects()
            ->create($validated);

        $subject->classrooms()->sync($classroomIds);

        return to_route('lecturer.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject): View
    {
        Gate::authorize('view', $subject);

        $subject->load([
            'classrooms:id,name,code',
            'exams:id,subject_id,title,published_at',
        ]);

        return view(
            'lecturer.subjects.show',
            compact('subject'),
        );
    }

    public function edit(Subject $subject): View
    {
        Gate::authorize('update', $subject);

        $subject->load('classrooms:id');

        $classrooms = auth()->user()
            ->createdClassrooms()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view(
            'lecturer.subjects.edit',
            compact('subject', 'classrooms'),
        );
    }

    public function update(
        Request $request,
        Subject $subject,
    ): RedirectResponse {
        Gate::authorize('update', $subject);

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('subjects', 'code')->ignore($subject),
            ],
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('classrooms', 'id')->where(
                    fn ($query) => $query->where(
                        'created_by',
                        $request->user()->id,
                    ),
                ),
            ],
        ]);

        $classroomIds = $validated['classroom_ids'] ?? [];

        unset($validated['classroom_ids']);

        $subject->update($validated);
        $subject->classrooms()->sync($classroomIds);

        return to_route('lecturer.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        Gate::authorize('delete', $subject);

        if ($subject->exams()->exists()) {
            return to_route('lecturer.subjects.index')
                ->with(
                    'error',
                    'This subject cannot be deleted because it has exams.',
                );
        }

        $subject->delete();

        return to_route('lecturer.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
