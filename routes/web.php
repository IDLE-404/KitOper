<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirstCourseSchedulePageController;

Route::get('/', [FirstCourseSchedulePageController::class, 'index'])->name('home');

Route::prefix('first-course')->group(function () {
    Route::get('/schedule', [FirstCourseSchedulePageController::class, 'index'])->name('first.schedule.index');
    Route::get('/schedule/create', [FirstCourseSchedulePageController::class, 'create'])->name('first.schedule.create');
    Route::post('/schedule', [FirstCourseSchedulePageController::class, 'store'])->name('first.schedule.store');
});
