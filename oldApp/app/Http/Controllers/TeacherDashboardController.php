<?php

namespace App\Http\Controllers;

use App\Services\ScheduleToFormTwoSyncService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function today(Request $request): View
    {
        $user = $request->user();
        $teacherId = (int) ($user?->teacher_id ?? 0);
        $teacherName = null;
        if ($teacherId && Schema::hasTable('teachers')) {
            $teacherName = DB::table('teachers')
                ->where('id', $teacherId)
                ->value(DB::raw('COALESCE(initials, teacher_name)'));
        }

        $today = Carbon::today();
        $weekDay = (int) $today->isoWeekday(); // 1..7 (Mon..Sun)
        $dayMap = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
        ];
        $studyDay = $dayMap[$weekDay] ?? null;
        $lessons = [];

        if ($teacherId && $studyDay !== null) {
            /** @var ScheduleToFormTwoSyncService $syncService */
            $syncService = app(ScheduleToFormTwoSyncService::class);
            $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);

            foreach ([1, 2, 3, 4] as $course) {
                $tables = CourseContext::tables($course);
                if (!Schema::hasTable($tables['schedules']) || !Schema::hasTable($tables['groups']) || !Schema::hasTable($tables['subjects'])) {
                    continue;
                }

                $weekMode = $syncService->resolveWeekMode($weekStart, $course);
                $groupNames = DB::table($tables['groups'])->pluck('group_name', 'id')->all();
                $subjects = DB::table($tables['subjects'])
                    ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
                    ->get()
                    ->mapWithKeys(function ($row) use ($course) {
                        $name = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
                        $module = trim((string) ($row->module_title ?? ''));
                        $title = ($course !== 1 && $module !== '') ? trim($module . ' ' . $name) : $name;
                        return [(int) $row->id => $title];
                    })
                    ->all();

                $rows = DB::table($tables['schedules'])
                    ->whereDate('week_start', $weekStart->toDateString())
                    ->where('study_day', $studyDay)
                    ->orderBy('lesson_number')
                    ->orderBy('group_id')
                    ->get();

                foreach ($rows as $row) {
                    foreach ($this->slotsForTeacher($row, $weekMode) as $slot) {
                        if ((int) ($slot['teacher_id'] ?? 0) !== $teacherId) {
                            continue;
                        }

                        $lessons[] = [
                            'course' => $course,
                            'group_name' => $groupNames[(int) ($row->group_id ?? 0)] ?? ('Группа ' . (int) ($row->group_id ?? 0)),
                            'lesson_number' => (int) ($row->lesson_number ?? 0),
                            'subgroup' => (int) ($slot['subgroup'] ?? 1),
                            'subject_name' => $subjects[(int) ($slot['subject_id'] ?? 0)] ?? '—',
                            'room' => $slot['room'] ?? null,
                            'week_mode_label' => $slot['week_mode_label'] ?? 'Обе недели',
                        ];
                    }
                }
            }
        }

        usort($lessons, function (array $a, array $b): int {
            $byLesson = ($a['lesson_number'] ?? 0) <=> ($b['lesson_number'] ?? 0);
            if ($byLesson !== 0) {
                return $byLesson;
            }
            $byCourse = ($a['course'] ?? 0) <=> ($b['course'] ?? 0);
            if ($byCourse !== 0) {
                return $byCourse;
            }
            return strcmp((string) ($a['group_name'] ?? ''), (string) ($b['group_name'] ?? ''));
        });

        $deduped = [];
        foreach ($lessons as $lesson) {
            $key = implode('|', [
                $lesson['course'] ?? '',
                $lesson['group_name'] ?? '',
                $lesson['lesson_number'] ?? '',
                $lesson['subgroup'] ?? '',
                $lesson['subject_name'] ?? '',
                $lesson['room'] ?? '',
                $lesson['week_mode_label'] ?? '',
            ]);
            $deduped[$key] = $lesson;
        }

        return view('teacher.today', [
            'teacherName' => $teacherName,
            'teacherLinked' => $teacherId > 0,
            'today' => $today,
            'studyDay' => $studyDay,
            'lessons' => array_values($deduped),
        ]);
    }

    protected function slotsForTeacher(object $row, string $weekMode): array
    {
        $slots = [];
        $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? 2 : 1;

        $slot1 = $this->slotForSubgroup($row, 1, $weekMode, $subgroupFlag);
        if ($slot1 !== null) {
            $slots[] = $slot1;
        }

        $slot2 = $this->slotForSubgroup($row, 2, $weekMode, $subgroupFlag);
        if ($slot2 !== null) {
            $slots[] = $slot2;
        }

        return $slots;
    }

    protected function slotForSubgroup(object $row, int $subgroup, string $weekMode, int $subgroupFlag): ?array
    {
        if ($subgroup === 1) {
            $numTeacher = $subgroupFlag === 1 ? ($row->teacher_id ?? null) : null;
            $numSubject = $subgroupFlag === 1 ? ($row->subject_id ?? null) : null;
            $numRoom = $subgroupFlag === 1 ? ($row->room_id ?? null) : null;
            $denTeacher = $subgroupFlag === 1 ? ($row->teacher_id_denominator ?? null) : null;
            $denSubject = $subgroupFlag === 1 ? ($row->subject_id_denominator ?? null) : null;
            $denRoom = $subgroupFlag === 1 ? ($row->room_id_denominator ?? null) : null;
        } else {
            $numTeacher = ($row->teacher_id_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->teacher_id ?? null) : null);
            $numSubject = ($row->subject_id_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->subject_id ?? null) : null);
            $numRoom = ($row->room_id_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->room_id ?? null) : null);
            $denTeacher = ($row->teacher_id_denominator_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->teacher_id_denominator ?? null) : null);
            $denSubject = ($row->subject_id_denominator_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->subject_id_denominator ?? null) : null);
            $denRoom = ($row->room_id_denominator_2 ?? null) ?: ($subgroupFlag === 2 ? ($row->room_id_denominator ?? null) : null);
        }

        $hasDen = (bool) ($denTeacher || $denSubject || $denRoom
            || ($row->{"is_absent_{$subgroup}_den"} ?? false)
            || ($row->{"is_replacement_{$subgroup}_den"} ?? false)
            || ($row->{"replacement_teacher_id_{$subgroup}_den"} ?? null)
            || ($row->{"replacement_subject_id_{$subgroup}_den"} ?? null));
        $useDen = $hasDen && $weekMode === 'denominator';
        $modeSuffix = $useDen ? 'den' : 'num';

        $teacher = $useDen ? ($denTeacher ?: $numTeacher) : $numTeacher;
        $subject = $useDen ? ($denSubject ?: $numSubject) : $numSubject;
        $room = $useDen ? ($denRoom ?: $numRoom) : $numRoom;

        $isAbsent = (bool) ($row->{"is_absent_{$subgroup}_{$modeSuffix}"} ?? false);
        $isReplacement = (bool) ($row->{"is_replacement_{$subgroup}_{$modeSuffix}"} ?? false);
        $replacementTeacher = $isReplacement ? ($row->{"replacement_teacher_id_{$subgroup}_{$modeSuffix}"} ?? null) : null;
        $replacementSubject = $isReplacement ? ($row->{"replacement_subject_id_{$subgroup}_{$modeSuffix}"} ?? null) : null;

        if ($replacementTeacher) {
            $teacher = $replacementTeacher;
            if ($replacementSubject) {
                $subject = $replacementSubject;
            }
        } elseif ($isAbsent) {
            $teacher = null;
            $subject = null;
            $room = null;
        }

        if (!$teacher && !$subject && !$room) {
            return null;
        }

        return [
            'subgroup' => $subgroup,
            'teacher_id' => $teacher ? (int) $teacher : null,
            'subject_id' => $subject ? (int) $subject : null,
            'room' => $room ? (string) $room : null,
            'week_mode_label' => $hasDen ? ($weekMode === 'denominator' ? 'Знаменатель' : 'Числитель') : 'Обе недели',
        ];
    }
}
