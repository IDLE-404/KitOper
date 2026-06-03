<?php

namespace App\Http\Controllers;

use App\Services\ScheduleToFormTwoSyncService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherWorkloadController extends Controller
{
    public function index(Request $request, ScheduleToFormTwoSyncService $syncService)
    {
        $weekStartInput = $request->get('week_start');
        $weekStart = $weekStartInput
            ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $dayOrder = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
        ];

        $occupancy = [];
        $teacherNames = [];
        $maxLesson = 7;

        foreach ([1, 2, 3, 4] as $course) {
            $tables = CourseContext::tables($course);
            if (
                !Schema::hasTable($tables['teachers'])
                || !Schema::hasTable($tables['subjects'])
                || !Schema::hasTable($tables['groups'])
                || !Schema::hasTable($tables['schedules'])
            ) {
                continue;
            }

            $teachers = DB::table($tables['teachers'])->pluck('teacher_name', 'id')->all();
            foreach ($teachers as $name) {
                if ($name !== null && $name !== '') {
                    $teacherNames[$name] = true;
                }
            }

            $subjects = DB::table($tables['subjects'])
                ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
                ->get()
                ->mapWithKeys(function ($row) {
                    $title = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
                    return [$row->id => ['title' => $title, 'module' => $row->module_title]];
                })
                ->all();

            $groups = DB::table($tables['groups'])->pluck('group_name', 'id')->all();

            $rows = DB::table($tables['schedules'])
                ->whereDate('week_start', $weekStart->toDateString())
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $weekMode = $syncService->resolveWeekMode($weekStart, $course);

            foreach ($rows as $row) {
                $day = $row->study_day ?? null;
                $lesson = (int) ($row->lesson_number ?? 0);
                if (!$day || $lesson < 1 || $lesson > $maxLesson) {
                    continue;
                }

                $groupName = $groups[$row->group_id] ?? ('Группа ' . (int) ($row->group_id ?? 0));

                foreach ($this->subgroupsForRow($row) as $subgroup) {
                    [$subjectId, $teacherId] = $this->resolveActiveEntry($row, $subgroup, $weekMode);
                    if (!$teacherId) {
                        continue;
                    }

                    $teacherName = $teachers[$teacherId] ?? null;
                    if (!$teacherName) {
                        continue;
                    }

                    $subjectTitle = $this->formatSubjectTitle($subjects[$subjectId] ?? null, $course !== 1);

                    $occupancy[$teacherName][$day][$lesson][] = [
                        'lesson' => $lesson,
                        'subject' => $subjectTitle,
                        'group' => $groupName,
                        'group_id' => (int) ($row->group_id ?? 0),
                        'course' => $course,
                        'subgroup' => $subgroup,
                    ];
                }
            }
        }

        $teacherList = array_keys($teacherNames);
        sort($teacherList, SORT_FLAG_CASE | SORT_STRING);

        foreach ($teacherList as $teacherName) {
            foreach ($dayOrder as $day) {
                for ($lesson = 1; $lesson <= $maxLesson; $lesson++) {
                    if (empty($occupancy[$teacherName][$day][$lesson])) {
                        continue;
                    }
                    usort($occupancy[$teacherName][$day][$lesson], function (array $a, array $b) {
                        $subjectCmp = strcmp($a['subject'], $b['subject']);
                        if ($subjectCmp !== 0) {
                            return $subjectCmp;
                        }
                        return strcmp($a['group'], $b['group']);
                    });
                }
            }
        }

        return view('teachers.workload', [
            'teachers' => $teacherList,
            'days' => $dayOrder,
            'occupancy' => $occupancy,
            'weekStart' => $weekStart->toDateString(),
            'weekStartLabel' => $weekStart->format('d.m.Y'),
        ]);
    }

    protected function subgroupsForRow(object $row): array
    {
        $flag = $row->subgroup ?? null;
        if (in_array($flag, ['2', 'B'], true)) {
            return [2];
        }

        $hasSubgroup2Data = ($row->subject_id_2 ?? null)
            || ($row->teacher_id_2 ?? null)
            || ($row->subject_id_denominator_2 ?? null)
            || ($row->teacher_id_denominator_2 ?? null);

        return $hasSubgroup2Data ? [1, 2] : [1];
    }

    protected function resolveActiveEntry(object $row, int $subgroup, string $weekMode): array
    {
        $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? 2 : 1;
        $isSub2 = $subgroup === 2;

        if ($isSub2) {
            $subjectNum = $row->subject_id_2 ?? ($subgroupFlag === 2 ? $row->subject_id : null);
            $teacherNum = $row->teacher_id_2 ?? ($subgroupFlag === 2 ? $row->teacher_id : null);
            $subjectDen = $row->subject_id_denominator_2 ?? null;
            $teacherDen = $row->teacher_id_denominator_2 ?? null;
            $roomDen = $row->room_id_denominator_2 ?? null;
        } else {
            $subjectNum = $row->subject_id ?? null;
            $teacherNum = $row->teacher_id ?? null;
            $subjectDen = $row->subject_id_denominator ?? null;
            $teacherDen = $row->teacher_id_denominator ?? null;
            $roomDen = $row->room_id_denominator ?? null;
        }

        $hasDenominator = $subjectDen || $teacherDen || $roomDen;
        $useDenominator = $hasDenominator && $weekMode === 'denominator';

        $activeSubject = $useDenominator ? ($subjectDen ?: $subjectNum) : $subjectNum;
        $activeTeacher = $useDenominator ? ($teacherDen ?: $teacherNum) : $teacherNum;

        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $useDenominator ? '_den' : '_num';
        $isReplacementField = "is_replacement{$suffix}{$modeSuffix}";
        $replacementTeacherField = "replacement_teacher_id{$suffix}{$modeSuffix}";
        $replacementSubjectField = "replacement_subject_id{$suffix}{$modeSuffix}";
        $isAbsentField = "is_absent{$suffix}{$modeSuffix}";

        $isReplacement = (bool) ($row->{$isReplacementField} ?? false);
        $isAbsent = (bool) ($row->{$isAbsentField} ?? false);

        if ($isReplacement) {
            $replacementTeacherId = $row->{$replacementTeacherField} ?? null;
            $replacementSubjectId = $row->{$replacementSubjectField} ?? null;
            if ($replacementTeacherId) {
                $activeTeacher = $replacementTeacherId;
            }
            if ($replacementSubjectId) {
                $activeSubject = $replacementSubjectId;
            }
        } elseif ($isAbsent) {
            $activeTeacher = null;
        }

        return [$activeSubject, $activeTeacher];
    }

    protected function formatSubjectTitle(?array $subject, bool $includeModule): string
    {
        if (!$subject) {
            return '—';
        }

        $name = $subject['title'] ?? null;
        $module = trim((string) ($subject['module'] ?? ''));
        if ($includeModule && $module !== '') {
            return trim($module . ' ' . ($name ?: ''));
        }

        return $name ?: '—';
    }
}
