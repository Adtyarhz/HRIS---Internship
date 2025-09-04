<?php

namespace App\Http\Controllers;

use App\Models\KpiPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiPeriodController extends Controller
{
    /**
     * Menampilkan daftar periode KPI dan memeriksa/membuat periode otomatis.
     */
    public function index()
    {
        // Perbarui status periode terlebih dahulu
        $this->updatePeriodStatuses();

        // Periksa dan buat periode otomatis untuk periode saat ini
        $this->generateAutomaticPeriods();

        $kpiPeriods = KpiPeriod::latest()->paginate(10);
        return view('kpi.periods.index', compact('kpiPeriods'));
    }

    /**
     * Menampilkan form untuk membuat periode KPI manual.
     */
    public function create()
    {
        return view('kpi.periods.create');
    }

    /**
     * Menyimpan periode KPI manual.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'period_name' => 'required|string|max:255|unique:kpi_periods,period_name',
            'start_date' => [
                'required',
                'date',
                // Validasi untuk memastikan tidak ada periode lain dengan start_date dan end_date yang sama
                Rule::unique('kpi_periods')->where(function ($query) use ($request) {
                    return $query->where('start_date', $request->start_date)
                                 ->where('end_date', $request->end_date);
                }),
            ],
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(['Aktif', 'Ditutup'])],
        ], [
            'start_date.unique' => 'Periode dengan tanggal mulai dan selesai yang sama sudah ada. Silakan gunakan nama periode lain atau ubah tanggal.',
        ]);

        KpiPeriod::create($validatedData);
        return redirect()->route('kpi-periods.index')->with('success', 'Periode KPI manual berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit periode KPI.
     */
    public function edit(KpiPeriod $kpiPeriod)
    {
        return view('kpi.periods.edit', compact('kpiPeriod'));
    }

    /**
     * Memperbarui periode KPI.
     */
    public function update(Request $request, KpiPeriod $kpiPeriod)
    {
        $validatedData = $request->validate([
            'period_name' => ['required', 'string', 'max:255', Rule::unique('kpi_periods')->ignore($kpiPeriod->id)],
            'start_date' => [
                'required',
                'date',
                // Validasi untuk memastikan tidak ada periode lain dengan start_date dan end_date yang sama
                Rule::unique('kpi_periods')->where(function ($query) use ($request) {
                    return $query->where('start_date', $request->start_date)
                                 ->where('end_date', $request->end_date);
                })->ignore($kpiPeriod->id),
            ],
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(['Aktif', 'Ditutup'])],
        ], [
            'start_date.unique' => 'Periode dengan tanggal mulai dan selesai yang sama sudah ada. Silakan gunakan nama periode lain atau ubah tanggal.',
        ]);

        $kpiPeriod->update($validatedData);
        return redirect()->route('kpi-periods.index')->with('success', 'Periode KPI berhasil diperbarui.');
    }

    /**
     * Menghapus periode KPI jika tidak digunakan.
     */
    public function destroy(KpiPeriod $kpiPeriod)
    {
        if ($kpiPeriod->assessments()->exists()) {
            return redirect()->route('kpi-periods.index')->with('error', 'Gagal! Periode ini sudah digunakan dalam penilaian dan tidak dapat dihapus.');
        }
        $kpiPeriod->delete();
        return redirect()->route('kpi-periods.index')->with('success', 'Periode KPI berhasil dihapus.');
    }

    /**
     * Membuat periode otomatis untuk periode saat ini berdasarkan tipe periode.
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

        foreach ($periodTypes as $type => $name) {
            $period = null;

            // Cek apakah sudah ada periode aktif untuk tipe ini
            $existingPeriod = KpiPeriod::where('period_name', $name)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->where('status', 'Aktif')
                ->first();

            if ($existingPeriod) {
                continue; // Lewati jika periode aktif sudah ada
            }

            if ($type === 'tahunan') {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
                if ($now->between($startDate, $endDate)) {
                    $period = [
                        'period_name' => $name,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Aktif',
                    ];
                }
            } elseif ($type === 'bulanan') {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $period = [
                    'period_name' => $name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'Aktif',
                ];
            } elseif ($type === 'triwulan') {
                $quarter = ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endMonth = $startMonth + 2;
                $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();
                if ($now->between($startDate, $endDate)) {
                    $period = [
                        'period_name' => $name,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Aktif',
                    ];
                }
            } elseif ($type === 'per_4_bulan') {
                $tertial = ceil($now->month / 4);
                $startMonth = ($tertial - 1) * 4 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endMonth = $startMonth + 3;
                $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();
                if ($now->between($startDate, $endDate)) {
                    $period = [
                        'period_name' => $name,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Aktif',
                    ];
                }
            } elseif ($type === 'per_6_bulan') {
                $semester = ceil($now->month / 6);
                $startMonth = ($semester - 1) * 6 + 1;
                $startDate = Carbon::create($year, $startMonth, 1);
                $endMonth = $startMonth + 5;
                $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();
                if ($now->between($startDate, $endDate)) {
                    $period = [
                        'period_name' => $name,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Aktif',
                    ];
                }
            } elseif ($type === 'mingguan') {
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
                $period = [
                    'period_name' => $name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'Aktif',
                ];
            }

            if ($period && !KpiPeriod::where([
                'period_name' => $period['period_name'],
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date']
            ])->exists()) {
                KpiPeriod::create($period);
            }
        }
    }

    /**
     * Memperbarui status periode secara otomatis berdasarkan tanggal saat ini.
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