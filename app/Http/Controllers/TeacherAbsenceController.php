<?php

namespace App\Http\Controllers;

use App\Support\TeacherAbsenceTypes;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAbsenceController extends Controller
{
    public function index()
    {
        $absences = DB::table('teacher_absences as a')
            ->join('teachers as t', 't.id', '=', 'a.teacher_id')
            ->select('a.*', 't.teacher_name')
            ->orderByDesc('a.start_date')
            ->orderBy('t.teacher_name')
            ->get();

        $teachers = DB::table('teachers')
            ->select('id', 'teacher_name')
            ->orderBy('teacher_name')
            ->get();

        return view('teacher_absences.index', [
            'absences' => $absences,
            'teachers' => $teachers,
            'absenceTypes' => TeacherAbsenceTypes::labels(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'type' => 'required|string|in:' . implode(',', TeacherAbsenceTypes::values()),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::table('teacher_absences')->insert([
            'teacher_id' => $data['teacher_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('teacher_absences.index')->with('success', 'Период отсутствия добавлен.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'type' => 'required|string|in:' . implode(',', TeacherAbsenceTypes::values()),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::table('teacher_absences')
            ->where('id', $id)
            ->update([
                'teacher_id' => $data['teacher_id'],
                'type' => $data['type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'notes' => $data['notes'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('teacher_absences.index')->with('success', 'Период отсутствия обновлен.');
    }

    public function destroy(int $id)
    {
        try {
            DB::table('teacher_absences')->where('id', $id)->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('teacher_absences.index')
                ->withErrors(['delete' => 'Не удалось удалить запись.']);
        }

        return redirect()->route('teacher_absences.index')->with('success', 'Запись удалена.');
    }
}
