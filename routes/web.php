<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirstCourseSchedulePageController;

Route::get('/', [FirstCourseSchedulePageController::class, 'index'])->name('home');

Route::prefix('first-course')->group(function () {
    Route::get('/schedule', [FirstCourseSchedulePageController::class, 'index'])->name('first.schedule.index');
    Route::get('/schedule/week', [FirstCourseSchedulePageController::class, 'week'])->name('first.schedule.week');
    Route::post('/schedule/week', [FirstCourseSchedulePageController::class, 'weekSave'])->name('first.schedule.week.save');
    Route::post('/schedule/expand-semester', [FirstCourseSchedulePageController::class, 'expandSemester'])->name('first.schedule.semester.expand');
    Route::post('/schedule/update-pair', [FirstCourseSchedulePageController::class, 'updatePair'])->name('first.schedule.pair.update');
    Route::get('/form-two', [\App\Http\Controllers\FormTwoController::class, 'index'])->name('first.schedule.form_two');
    Route::post('/form-two/save', [\App\Http\Controllers\FormTwoController::class, 'save'])->name('first.schedule.form_two.save');
});
