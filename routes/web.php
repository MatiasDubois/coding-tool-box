<?php

use App\Http\Controllers\CohortController;
use App\Http\Controllers\CommonLifeController;
use App\Http\Controllers\CommonLifeTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetroController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Redirect the root path to /dashboard
Route::redirect('/', 'dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('verified')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Cohorts
        Route::get('/cohorts', [CohortController::class, 'index'])->name('cohort.index');
        Route::get('/cohort/{cohort}', [CohortController::class, 'show'])->name('cohort.show');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teacher.index');

        // Students
        Route::get('students', [StudentController::class, 'index'])->name('student.index');

        // Knowledge
        Route::get('knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');

        // Groups
        Route::get('groups', [GroupController::class, 'index'])->name('group.index');

        // Retro
        route::get('retros', [RetroController::class, 'index'])->name('retro.index');

        // Common life
        Route::get('common-life', [CommonLifeController::class, 'index'])->name('common-life.index');

        // Common life task
        Route::get('common-life/task', [CommonLifeTaskController::class, 'index'])->name('tasks.index');
        Route::get('common-life/task/create', [CommonLifeTaskController::class, 'create'])->name('tasks.create');
        Route::post('common-life/task', [CommonLifeTaskController::class, 'store'])->name('tasks.store');
        Route::get('common-life/task/{task}/edit', [CommonLifeTaskController::class, 'edit'])->name('tasks.edit');
        Route::put('common-life/task/{task}', [CommonLifeTaskController::class, 'update'])->name('tasks.update');
        Route::delete('common-life/task/{task}', [CommonLifeTaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('common-life/tasks/{task}/toggle-complete', [CommonLifeTaskController::class, 'toggleComplete'])->name('tasks.toggleComplete');
        Route::post('common-life/tasks/{task}/comment', [CommonLifeTaskController::class, 'comment'])->name('tasks.comment');
        Route::get('common-life/task/history' , [CommonLifeTaskController::class, 'history'])->name('tasks.history');
        Route::patch('common-life/tasks/{task}/unvalidate/{user}', [CommonLifeTaskController::class, 'unvalidate'])->name('tasks.unvalidate');
    });
});

require __DIR__.'/auth.php';
