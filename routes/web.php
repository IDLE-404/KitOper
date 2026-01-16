<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirstCourseSchedulePageController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherWorkloadController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\GroupController;

Route::get('/', [FirstCourseSchedulePageController::class, 'index'])->name('home');
Route::get('/teachers/workload', [TeacherWorkloadController::class, 'index'])->name('teachers.workload');
Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
Route::post('/practice', [PracticeController::class, 'store'])->name('practice.store');
Route::delete('/practice/{practicePeriod}', [PracticeController::class, 'destroy'])->name('practice.destroy');
Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

Route::prefix('first-course')->group(function () {
    Route::get('/schedule', [FirstCourseSchedulePageController::class, 'index'])->name('first.schedule.index');
    Route::get('/schedule/week', [FirstCourseSchedulePageController::class, 'week'])->name('first.schedule.week');
    Route::post('/schedule/week', [FirstCourseSchedulePageController::class, 'weekSave'])->name('first.schedule.week.save');
    Route::post('/schedule/expand-semester', [FirstCourseSchedulePageController::class, 'expandSemester'])->name('first.schedule.semester.expand');
    Route::post('/schedule/update-pair', [FirstCourseSchedulePageController::class, 'updatePair'])->name('first.schedule.pair.update');
    Route::post('/schedule/delete-pair', [FirstCourseSchedulePageController::class, 'deletePair'])->name('first.schedule.pair.delete');
    Route::get('/form-two', [\App\Http\Controllers\FormTwoController::class, 'index'])->name('first.schedule.form_two');
    Route::post('/form-two/save', [\App\Http\Controllers\FormTwoController::class, 'save'])->name('first.schedule.form_two.save');
    Route::get('/form-two/export', [\App\Http\Controllers\FormTwoController::class, 'export'])->name('first.schedule.form_two.export');
    Route::get('/form-two/export-semester', [\App\Http\Controllers\FormTwoController::class, 'exportSemester'])->name('first.schedule.form_two.export_semester');
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::put('/groups/{id}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
});
