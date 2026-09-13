<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ExamResultController;
use App\Http\Controllers\Admin\LearnerStatisticsController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\TryoutController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\StudentDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Aplikasi privat satu pengguna: akses utama diarahkan ke login atau beranda.
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('student.dashboard')
        : redirect()->route('login');
});

// Otentikasi (satu akun, tanpa pendaftaran)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Latihan harian
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');

    Route::post('/latihan/{tryout}/mulai', [ExamController::class, 'start'])->name('student.exam.start');
    Route::get('/latihan/sesi/{attempt}', [ExamController::class, 'room'])->name('student.exam.room');
    Route::post('/latihan/sesi/{attempt}/jawab', [ExamController::class, 'saveAnswer'])->name('student.exam.answer');
    Route::post('/latihan/sesi/{attempt}/selesai', [ExamController::class, 'finish'])->name('student.exam.finish');
    Route::get('/latihan/sesi/{attempt}/hasil', [ExamController::class, 'result'])->name('student.exam.result');
    Route::get('/latihan/sesi/{attempt}/pembahasan', [ExamController::class, 'review'])->name('student.exam.review');
});

// Panel pengelolaan soal
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('tryouts', TryoutController::class)->except(['show']);

    Route::get('tryouts/{tryout}/questions', [QuestionController::class, 'index'])->name('tryouts.questions.index');
    Route::get('tryouts/{tryout}/questions/create', [QuestionController::class, 'create'])->name('tryouts.questions.create');
    Route::get('tryouts/{tryout}/questions/import', [QuestionImportController::class, 'create'])->name('tryouts.questions.import.create');
    Route::get('tryouts/{tryout}/questions/import/template', [QuestionImportController::class, 'template'])->name('tryouts.questions.import.template');
    Route::post('tryouts/{tryout}/questions/import', [QuestionImportController::class, 'store'])->name('tryouts.questions.import.store');
    Route::post('tryouts/{tryout}/questions', [QuestionController::class, 'store'])->name('tryouts.questions.store');
    Route::get('tryouts/{tryout}/questions/{question}/edit', [QuestionController::class, 'edit'])->name('tryouts.questions.edit');
    Route::put('tryouts/{tryout}/questions/{question}', [QuestionController::class, 'update'])->name('tryouts.questions.update');
    Route::delete('tryouts/{tryout}/questions/{question}', [QuestionController::class, 'destroy'])->name('tryouts.questions.destroy');

    Route::get('/statistik', [LearnerStatisticsController::class, 'index'])->name('statistics.index');

    Route::get('/results', [ExamResultController::class, 'index'])->name('results.index');
    Route::get('/results/{attempt}', [ExamResultController::class, 'show'])->name('results.show');
});
