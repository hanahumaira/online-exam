<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        if ($request->user()->isLecturer()) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($request->user()->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        abort(403);
    }

    public function lecturer(): View
    {
        return view('lecturer.dashboard');
    }

    public function student(): View
    {
        return view('student.dashboard');
    }
}
