<?php

namespace App\Http\Controllers;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    private const ROOM_TYPES = [
        'standard' => 'Обычный',
        'computer' => 'Компьютерный',
        'lab' => 'Лаборатория',
    ];

    public function index()
    {
        if (!Schema::hasTable('rooms')) {
            abort(500, 'Таблица кабинетов не найдена.');
        }

        $hasIsActive = Schema::hasColumn('rooms', 'is_active');
        $rooms = DB::table('rooms')->orderBy('code')->get();

        return view('rooms.index', [
            'rooms' => $rooms,
            'roomTypes' => self::ROOM_TYPES,
            'hasIsActive' => $hasIsActive,
        ]);
    }

    public function store(Request $request)
    {
        $hasIsActive = Schema::hasColumn('rooms', 'is_active');
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|string|in:' . implode(',', array_keys(self::ROOM_TYPES)),
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => $hasIsActive ? 'sometimes|boolean' : 'nullable',
        ]);

        $payload = [
            'code' => trim($data['code']),
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasIsActive) {
            $payload['is_active'] = $request->boolean('is_active', true);
        }

        DB::table('rooms')->insert($payload);

        return redirect()->route('rooms.index')->with('success', 'Кабинет добавлен.');
    }

    public function update(Request $request, int $id)
    {
        $hasIsActive = Schema::hasColumn('rooms', 'is_active');
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|string|in:' . implode(',', array_keys(self::ROOM_TYPES)),
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => $hasIsActive ? 'sometimes|boolean' : 'nullable',
        ]);

        $payload = [
            'code' => trim($data['code']),
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_at' => now(),
        ];
        if ($hasIsActive) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        DB::table('rooms')
            ->where('id', $id)
            ->update($payload);

        return redirect()->route('rooms.index')->with('success', 'Кабинет обновлен.');
    }

    public function destroy(int $id)
    {
        try {
            DB::table('rooms')->where('id', $id)->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('rooms.index')
                ->withErrors(['delete' => 'Не удалось удалить кабинет: есть связанные записи.']);
        }

        return redirect()->route('rooms.index')->with('success', 'Кабинет удален.');
    }
}
