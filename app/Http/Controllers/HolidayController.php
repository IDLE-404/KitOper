<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::query()
            ->orderByRaw('COALESCE(year, 9999) asc')
            ->orderBy('start_month')
            ->orderBy('start_day')
            ->get();

        return view('holidays.index', [
            'holidays' => $holidays,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateHoliday($request);
        Holiday::create($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Праздник добавлен.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $data = $this->validateHoliday($request);
        $holiday->update($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Праздник обновлен.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Праздник удален.');
    }

    private function validateHoliday(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $start = Carbon::create(2000, $startDate->month, $startDate->day);
        $end = Carbon::create(2000, $endDate->month, $endDate->day);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Дата окончания раньше даты начала.',
            ]);
        }

        $data = [
            'name' => $data['name'],
            'start_month' => $startDate->month,
            'start_day' => $startDate->day,
            'end_month' => $endDate->month,
            'end_day' => $endDate->day,
            'year' => null,
            'is_active' => true,
        ];

        return $data;
    }
}
