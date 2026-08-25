<?php

use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lecturer\ClassroomController;
use App\Http\Controllers\Lecturer\ExamController;
use App\Http\Controllers\Lecturer\QuestionController;
use App\Http\Controllers\Lecturer\StudentClassroomAssignmentController;
use App\Http\Controllers\Lecturer\SubjectController;
use App\Http\Controllers\Lecturer\ExamClassroomAssignmentController;
use App\Http\Controllers\Lecturer\ExamPublishingController;
use App\Http\Controllers\Student\AttemptController;
use App\Http\Controllers\Lecturer\GradingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Lecturer\ResultController as LecturerResultController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:lecturer')
        ->prefix('lecturer')
        ->name('lecturer.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'lecturer'])->name('dashboard');
            Route::resource('classrooms', ClassroomController::class);
            Route::resource('subjects', SubjectController::class);
            Route::get('students', [StudentClassroomAssignmentController::class, 'index'])->name('students.index');
            Route::get('students/{student}/classroom', [StudentClassroomAssignmentController::class, 'edit'])->name('students.classroom.edit');
            Route::put('students/{student}/classroom', [StudentClassroomAssignmentController::class, 'update'])->name('students.classroom.update');
            Route::resource('exams', ExamController::class);
            Route::get('exams/{exam}/questions/create', [QuestionController::class, 'create'])->name('exams.questions.create');
            Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('exams.questions.store');
            Route::get('exams/{exam}/questions/{question}/edit', [QuestionController::class, 'edit'])->name('exams.questions.edit');
            Route::put('exams/{exam}/questions/{question}', [QuestionController::class, 'update'])->name('exams.questions.update');
            Route::delete('exams/{exam}/questions/{question}', [QuestionController::class, 'destroy'])->name('exams.questions.destroy');
            Route::get('exams/{exam}/classrooms', [ExamClassroomAssignmentController::class, 'edit'])->name('exams.classrooms.edit');
            Route::put('exams/{exam}/classrooms', [ExamClassroomAssignmentController::class, 'update'])->name('exams.classrooms.update');
            Route::post('exams/{exam}/publish', [ExamPublishingController::class, 'store'])->name('exams.publish');
            Route::get('grading', [GradingController::class, 'index'])->name('grading.index');
            Route::get('grading/{attempt}', [GradingController::class, 'show'])->name('grading.show');
            Route::put('grading/{attempt}', [GradingController::class, 'update'])->name('grading.update');
            Route::get('results', [LecturerResultController::class, 'index'])->name('results.index');
            Route::get('results/{exam}', [LecturerResultController::class, 'show'])->name('results.show');
            Route::post('results/{exam}/release', [LecturerResultController::class, 'release'])->name('results.release');
        });

    Route::middleware('role:student')
        ->prefix('student')
        ->name('student.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
            Route::get('/exams', [StudentExamController::class, 'index'])->name('exams.index');
            Route::get('/exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
            Route::post('exams/{exam}/attempts', [AttemptController::class, 'store'])->name('exams.attempts.store');
            Route::get('attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
            Route::put('attempts/{attempt}', [AttemptController::class, 'update'])->name('attempts.update');
            Route::get('results', [StudentResultController::class, 'index'])->name('results.index');
            Route::get('results/{attempt}', [StudentResultController::class, 'show'])->name('results.show');
        });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
