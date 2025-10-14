<?php

namespace App\Http\Controllers;

use App\Models\KpiPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiPeriodController extends Controller
{
    /**
     * Display a listing of KPI periods and check/create automatic periods.
     */
    public function index()
    {
        // Update period statuses first
        $this->updatePeriodStatuses();

        // Generate automatic periods for the current period
        $this->generateAutomaticPeriods();

        // Retrieve only active periods
        $kpiPeriods = KpiPeriod::where('status', 'Active')
            ->orderByDesc('start_date')
            ->paginate(10);
        return view('kpi.periods.index', compact('kpiPeriods'));
    }

    /**
     * Display the form for creating a manual KPI period.
     */
    public function create()
    {
        return view('kpi.periods.create');
    }

    /**
     * Store a manually created KPI period.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'period_name' => 'required|string|max:255|unique:kpi_periods,period_name',
            'start_date' => [
                'required',
                'date',
                // Ensure no other period has the same start_date and end_date
                Rule::unique('kpi_periods')->where(function ($query) use ($request) {
                    return $query->where('start_date', $request->start_date)
                        ->where('end_date', $request->end_date);
                }),
            ],
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(['Aktif', 'Ditutup'])],
        ], [
            'start_date.unique' => 'A period with the same start and end dates already exists. Please use a different period name or change the dates.',
        ]);

        KpiPeriod::create($validatedData);
        return redirect()->route('kpi-periods.index')->with('success', 'KPI period created successfully.');
    }

    /**
     * Display the form for editing a KPI period.
     */
    public function edit(KpiPeriod $kpiPeriod)
    {
        return view('kpi.periods.edit', compact('kpiPeriod'));
    }

    /**
     * Update a KPI period.
     */
    public function update(Request $request, KpiPeriod $kpiPeriod)
    {
        $validatedData = $request->validate([
            'period_name' => ['required', 'string', 'max:255', Rule::unique('kpi_periods')->ignore($kpiPeriod->id)],
            'start_date' => [
                'required',
                'date',
                // Ensure no other period has the same start_date and end_date
                Rule::unique('kpi_periods')->where(function ($query) use ($request) {
                    return $query->where('start_date', $request->start_date)
                        ->where('end_date', $request->end_date);
                })->ignore($kpiPeriod->id),
            ],
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(['Aktif', 'Ditutup'])],
        ], [
            'start_date.unique' => 'A period with the same start and end dates already exists. Please use a different period name or change the dates.',
        ]);

        $kpiPeriod->update($validatedData);
        return redirect()->route('kpi-periods.index')->with('success', 'KPI period updated successfully.');
    }

    /**
     * Delete a KPI period if it is not used.
     */
    public function destroy(KpiPeriod $kpiPeriod)
    {
        if ($kpiPeriod->assessments()->exists()) {
            return redirect()->route('kpi-periods.index')->with('error', 'Failed! This period is used in assessments and cannot be deleted.');
        }
        $kpiPeriod->delete();
        return redirect()->route('kpi-periods.index')->with('success', 'KPI period deleted successfully.');
    }

    /**
     * Generate automatic periods for the current period based on period type.
     */
    protected function generateAutomaticPeriods()
    {
        $now = Carbon::now();
        $year = $now->year;
        $periodTypes = [
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            'triwulan' => 'Triwulan',
            'per_4_bulan' => '4 Bulanan',
            'per_6_bulan' => 'Semesteran',
            'tahunan' => 'Tahunan',
        ];

        foreach ($periodTypes as $type => $baseName) {
            $startDate = null;
            $endDate = null;

            if ($type === 'tahunan') {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
            } elseif ($type === 'bulanan') {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
            } elseif ($type === 'triwulan') {
                $quarter = ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endDate = Carbon::create($year, $startMonth + 2, 1)->endOfMonth();
            } elseif ($type === 'per_4_bulan') {
                $tertial = ceil($now->month / 4);
                $startMonth = ($tertial - 1) * 4 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endDate = Carbon::create($year, $startMonth + 3, 1)->endOfMonth();
            } elseif ($type === 'per_6_bulan') {
                $semester = ceil($now->month / 6);
                $startMonth = ($semester - 1) * 6 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endDate = Carbon::create($year, $startMonth + 5, 1)->endOfMonth();
            } elseif ($type === 'mingguan') {
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
            }

            if (!$startDate || !$endDate) {
                continue;
            }

            // Display label
            $periodName = $baseName . ' (' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y') . ')';

            // Check for existing period with same start_date and end_date
            $exists = KpiPeriod::whereDate('start_date', $startDate->format('Y-m-d'))
                ->whereDate('end_date', $endDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                KpiPeriod::create([
                    'period_name' => $periodName,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'Aktif',
                ]);
            }
        }
    }

    /**
     * Update period statuses automatically based on the current date.
     */
    protected function updatePeriodStatuses()
    {
        $now = Carbon::now();
        KpiPeriod::where('end_date', '<', $now)
            ->where('status', 'Aktif')
            ->update(['status' => 'Ditutup']);

        KpiPeriod::where('end_date', '>=', $now)
            ->where('status', 'Ditutup')
            ->update(['status' => 'Aktif']);
    }
}