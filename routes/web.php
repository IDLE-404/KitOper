<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FirstCourseSchedulePageController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherWorkloadController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\FieldCampController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TeacherAbsenceController;
use App\Http\Controllers\FormTwoTemplateController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'audit'])->group(function () {
    Route::get('/', [FirstCourseSchedulePageController::class, 'index'])->name('home');

    Route::middleware('role:teacher')->group(function () {
        Route::get('/teacher/today', [TeacherDashboardController::class, 'today'])->name('teacher.today');
    });

    Route::prefix('first-course')->group(function () {
        Route::get('/schedule', [FirstCourseSchedulePageController::class, 'index'])->name('first.schedule.index');
        Route::get('/schedule/day', [FirstCourseSchedulePageController::class, 'day'])->name('first.schedule.day');
    });

    Route::middleware('role:dispatcher')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update_role');

        Route::get('/teachers/workload', [TeacherWorkloadController::class, 'index'])->name('teachers.workload');

        Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
        Route::post('/practice', [PracticeController::class, 'store'])->name('practice.store');
        Route::delete('/practice/{practicePeriod}', [PracticeController::class, 'destroy'])->name('practice.destroy');

        Route::get('/field-camps', [FieldCampController::class, 'index'])->name('field_camps.index');
        Route::post('/field-camps', [FieldCampController::class, 'store'])->name('field_camps.store');
        Route::delete('/field-camps/{fieldCampPeriod}', [FieldCampController::class, 'destroy'])->name('field_camps.destroy');

        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/teacher-absences', [TeacherAbsenceController::class, 'index'])->name('teacher_absences.index');
        Route::post('/teacher-absences', [TeacherAbsenceController::class, 'store'])->name('teacher_absences.store');
        Route::put('/teacher-absences/{id}', [TeacherAbsenceController::class, 'update'])->name('teacher_absences.update');
        Route::delete('/teacher-absences/{id}', [TeacherAbsenceController::class, 'destroy'])->name('teacher_absences.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

        Route::prefix('first-course')->group(function () {
            Route::get('/schedule/week', [FirstCourseSchedulePageController::class, 'week'])->name('first.schedule.week');
            Route::get('/schedule/week-duplicate', [FirstCourseSchedulePageController::class, 'duplicateWeekPage'])->name('first.schedule.week.duplicate');
            Route::get('/schedule/availability', [FirstCourseSchedulePageController::class, 'availability'])->name('first.schedule.availability');
            Route::get('/schedule/free-teachers', [FirstCourseSchedulePageController::class, 'freeTeachers'])->name('first.schedule.free_teachers');
            Route::get('/schedule/free-rooms', [FirstCourseSchedulePageController::class, 'freeRooms'])->name('first.schedule.free_rooms');
            Route::post('/schedule/week', [FirstCourseSchedulePageController::class, 'weekSave'])->name('first.schedule.week.save');
            Route::post('/schedule/week-duplicate', [FirstCourseSchedulePageController::class, 'duplicateWeekApply'])->name('first.schedule.week.duplicate.store');
            Route::post('/schedule/expand-semester', [FirstCourseSchedulePageController::class, 'expandSemester'])->name('first.schedule.semester.expand');
            Route::post('/schedule/update-pair', [FirstCourseSchedulePageController::class, 'updatePair'])->name('first.schedule.pair.update');
            Route::post('/schedule/delete-pair', [FirstCourseSchedulePageController::class, 'deletePair'])->name('first.schedule.pair.delete');
            Route::post('/schedule/auto-assign-rooms-day', [FirstCourseSchedulePageController::class, 'autoAssignRoomsDay'])->name('first.schedule.auto_assign_rooms_day');
            Route::post('/schedule/clear-rooms-day', [FirstCourseSchedulePageController::class, 'clearRoomsDay'])->name('first.schedule.clear_rooms_day');

            Route::get('/form-two', [\App\Http\Controllers\FormTwoController::class, 'index'])->name('first.schedule.form_two');
            Route::post('/form-two/save', [\App\Http\Controllers\FormTwoController::class, 'save'])->name('first.schedule.form_two.save');
            Route::get('/form-two/export', [\App\Http\Controllers\FormTwoController::class, 'export'])->name('first.schedule.form_two.export');
            Route::get('/form-two/export-semester', [\App\Http\Controllers\FormTwoController::class, 'exportSemester'])->name('first.schedule.form_two.export_semester');

            Route::get('/form-two/templates', [FormTwoTemplateController::class, 'index'])->name('form_two_templates.index');
            Route::post('/form-two/templates', [FormTwoTemplateController::class, 'store'])->name('form_two_templates.store');
            Route::put('/form-two/templates/{id}', [FormTwoTemplateController::class, 'update'])->name('form_two_templates.update');
            Route::delete('/form-two/templates/{id}', [FormTwoTemplateController::class, 'destroy'])->name('form_two_templates.destroy');
            Route::post('/form-two/templates/{templateId}/items', [FormTwoTemplateController::class, 'storeItem'])->name('form_two_templates.items.store');
            Route::put('/form-two/templates/items/{itemId}', [FormTwoTemplateController::class, 'updateItem'])->name('form_two_templates.items.update');
            Route::delete('/form-two/templates/items/{itemId}', [FormTwoTemplateController::class, 'destroyItem'])->name('form_two_templates.items.destroy');

            Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
            Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
            Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
            Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

            Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
            Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
            Route::put('/groups/{id}', [GroupController::class, 'update'])->name('groups.update');
            Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
            Route::post('/groups/finish-year', [GroupController::class, 'finishYear'])->name('groups.finish_year');

            Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        });
    });
});
