<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Classroom::class);

        $classrooms = auth()->user()
            ->createdClassrooms()
            ->withCount(['students', 'subjects'])
            ->latest()
            ->paginate(10);

        return view('lecturer.classrooms.index', compact('classrooms'));
    }

    public function create(): View
    {
        Gate::authorize('create', Classroom::class);

        return view('lecturer.classrooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Classroom::class);

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
                'unique:classrooms,code',
            ],
        ]);

        auth()->user()->createdClassrooms()->create($validated);

        return to_route('lecturer.classrooms.index')
            ->with('success', 'Classroom created successfully.');
    }

    public function show(Classroom $classroom): View
    {
        Gate::authorize('view', $classroom);

        $classroom->load([
            'students:id,name,email,classroom_id',
            'subjects:id,name,code',
        ]);

        return view('lecturer.classrooms.show', compact('classroom'));
    }

    public function edit(Classroom $classroom): View
    {
        Gate::authorize('update', $classroom);

        return view('lecturer.classrooms.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        Gate::authorize('update', $classroom);

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
                Rule::unique('classrooms', 'code')->ignore($classroom),
            ],
        ]);

        $classroom->update($validated);

        return to_route('lecturer.classrooms.index')
            ->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        Gate::authorize('delete', $classroom);

        $classroom->delete();

        return to_route('lecturer.classrooms.index')
            ->with('success', 'Classroom deleted successfully.');
    }
}
