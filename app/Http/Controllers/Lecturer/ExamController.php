<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Exam::class);

        $exams = $request->user()
            ->createdExams()
            ->with('subject:id,name,code')
            ->withCount(['questions', 'classrooms'])
            ->latest()
            ->paginate(10);

        return view(
            'lecturer.exams.index',
            compact('exams'),
        );
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Exam::class);

        $subjects = $request->user()
            ->createdSubjects()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view(
            'lecturer.exams.create',
            compact('subjects'),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Exam::class);

        $request->merge([
            'title' => trim(
                (string) $request->input('title'),
            ),
            'instructions' => $request->filled('instructions')
                ? trim((string) $request->input('instructions'))
                : null,
        ]);

        $validated = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->where(
                        'created_by',
                        $request->user()->id,
                    ),
                ),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'instructions' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'duration_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:480',
            ],
        ]);

        $exam = $request->user()
            ->createdExams()
            ->create($validated);

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam): View
    {
        Gate::authorize('view', $exam);

        $exam->load([
            'subject:id,name,code',
            'questions.options:id,question_id,text,is_correct',
            'classrooms:id,name,code,created_by',
        ]);

        $exam->loadCount(['questions', 'classrooms']);

        return view(
            'lecturer.exams.show',
            compact('exam'),
        );
    }

    public function edit(
        Request $request,
        Exam $exam,
    ): View {
        Gate::authorize('update', $exam);

        $subjects = $request->user()
            ->createdSubjects()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view(
            'lecturer.exams.edit',
            compact('exam', 'subjects'),
        );
    }

    public function update(
        Request $request,
        Exam $exam,
    ): RedirectResponse {
        Gate::authorize('update', $exam);

        $request->merge([
            'title' => trim(
                (string) $request->input('title'),
            ),
            'instructions' => $request->filled('instructions')
                ? trim((string) $request->input('instructions'))
                : null,
        ]);

        $validated = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->where(
                        'created_by',
                        $request->user()->id,
                    ),
                ),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'instructions' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'duration_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:480',
            ],
        ]);

        $exam->update($validated);

        return to_route('lecturer.exams.show', $exam)
            ->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        Gate::authorize('delete', $exam);

        $exam->delete();

        return to_route('lecturer.exams.index')
            ->with('success', 'Exam deleted successfully.');
    }
}
