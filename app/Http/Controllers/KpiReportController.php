<?php

namespace App\Http\Controllers;

use App\Models\KpiAssessment;
use App\Models\Division;
use App\Models\Position;
use App\Models\KpiPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\KpiReportExport;

class KpiReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $assessments = collect();
        $divisions = collect();
        $positions = collect();

        // Manual Periods
        $manualPeriods = KpiPeriod::whereNotIn('period_name', [
            'Mingguan',
            'Bulanan',
            'Triwulan',
            '4 Bulanan',
            'Semesteran',
            'Tahunan'
        ])->orderBy('start_date', 'desc')->get();

        // Automatic Periods
        $specialPeriods = [
            ['id' => 'auto_mingguan', 'name' => 'Mingguan'],
            ['id' => 'auto_bulanan', 'name' => 'Bulanan'],
            ['id' => 'auto_triwulan', 'name' => 'Triwulan'],
            ['id' => 'auto_4_bulanan', 'name' => '4 Bulanan'],
            ['id' => 'auto_semesteran', 'name' => 'Semesteran'],
            ['id' => 'auto_tahunan', 'name' => 'Tahunan'],
        ];

        $periods = [
            'manual' => $manualPeriods,
            'special' => $specialPeriods,
        ];

        // Access control
        if ($user->role === 'hc' || $user->role === 'superadmin') {
            $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
            $positions = Position::orderBy('title')->get();
        } elseif ($user->role === 'manager') {
            if (!$user->employee || !$user->employee->division_id) {
                abort(403, 'Your division data is incomplete to access this report.');
            }

            if ($user->employee->position_id) {
                $subordinatePositionIds = $this->getAllSubordinatePositionIds($user->employee->position_id);
                if (!empty($subordinatePositionIds)) {
                    $positions = Position::whereIn('id', $subordinatePositionIds)->orderBy('title')->get();
                }
            }
        } else {
            abort(403, 'You do not have permission to view this report.');
        }

        // Apply filters if present
        $hasFilter = $request->filled('division_id') || $request->filled('position_id') || $request->filled('search') || $request->filled('period_id') || ($request->filled('start_date') && $request->filled('end_date'));

        if ($hasFilter) {
            $query = KpiAssessment::with(['employee.position', 'employee.division', 'period', 'supervisor'])
                ->select('kpi_assessments.*')
                ->join('employees', 'kpi_assessments.employee_id', '=', 'employees.id')
                ->join('positions', 'employees.position_id', '=', 'positions.id')
                ->join('kpi_periods', 'kpi_assessments.kpi_period_id', '=', 'kpi_periods.id');

            // Restrict data for managers
            if ($user->role === 'manager') {
                $query->where('employees.division_id', $user->employee->division_id);
            }

            if ($request->filled('division_id')) {
                $query->where('employees.division_id', $request->division_id);
            }
            if ($request->filled('position_id')) {
                $query->where('employees.position_id', $request->position_id);
            }
            if ($request->filled('search')) {
                $query->where('employees.full_name', 'like', '%' . $request->search . '%');
            }

            // Period filter
            if ($request->filled('period_id')) {
                $periodId = $request->period_id;

                if (str_starts_with($periodId, 'auto_')) {
                    $map = [
                        'mingguan' => 'Mingguan',
                        'bulanan' => 'Bulanan',
                        'triwulan' => 'Triwulan',
                        '4_bulanan' => '4 Bulanan',
                        'semesteran' => 'Semester',
                        'tahunan' => 'Tahunan',
                    ];
                    $key = str_replace('auto_', '', $periodId);
                    if (isset($map[$key])) {
                        $query->where('kpi_periods.period_name', 'like', $map[$key] . '%');
                    }
                } else {
                    $query->where('kpi_assessments.kpi_period_id', $periodId);
                }
            }

            // Date range filter
            $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();

                $q->where(function ($subQuery) use ($start, $end) {
                    $subQuery->where('kpi_periods.start_date', '>=', $start)
                        ->where('kpi_periods.end_date', '<=', $end);
                });
            });

            $assessments = $query->latest('kpi_assessments.created_at')->paginate(20)->withQueryString();
        }

        return view('kpi.reports.index', compact('assessments', 'divisions', 'positions', 'periods'));
    }

    public function show(KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();

        $hasPermission = false;

        if (in_array($user->role, ['hc', 'superadmin'])) {
            $hasPermission = true;
        } elseif ($user->role === 'manager' && 
                $user->employee && 
                $kpiAssessment->employee && 
                $user->employee->division_id === $kpiAssessment->employee->division_id) {
            $hasPermission = true;
        } elseif ($kpiAssessment->employee->user_id === $user->id || 
                $kpiAssessment->participants()->where('assessor_id', $user->id)->exists() || 
                $user->id === $kpiAssessment->primary_supervisor_id) {
            $hasPermission = true;
        }

        abort_unless($hasPermission, 403, 'You do not have permission to view this assessment detail.');

        $kpiAssessment->load(
            'assessmentItems.indicator',
            'assessmentItems.scoringRules',
            'assessmentItems.scores.participant',
            'participants.assessor',
            'employee',
            'period'
        );

        return view('kpi.reports.show', compact('kpiAssessment'));
    }

    private function getAllSubordinatePositionIds($parentPositionId)
    {
        $allSubordinateIds = [];
        $directSubordinateIds = Position::where('parent_id', $parentPositionId)->pluck('id')->toArray();
        $allSubordinateIds = array_merge($allSubordinateIds, $directSubordinateIds);

        foreach ($directSubordinateIds as $subordinateId) {
            $allSubordinateIds = array_merge($allSubordinateIds, $this->getAllSubordinatePositionIds($subordinateId));
        }

        return $allSubordinateIds;
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $query = KpiAssessment::with(['employee.position', 'employee.division', 'period', 'supervisor'])
            ->select('kpi_assessments.*')
            ->join('employees', 'kpi_assessments.employee_id', '=', 'employees.id')
            ->join('positions', 'employees.position_id', '=', 'positions.id')
            ->join('kpi_periods', 'kpi_assessments.kpi_period_id', '=', 'kpi_periods.id');

        // Restrict data for managers
        if ($user->role === 'manager') {
            $query->where('employees.division_id', $user->employee->division_id);
        }

        // Division filter
        if ($request->filled('division_id')) {
            $query->where('employees.division_id', $request->division_id);
        }

        // Position filter
        if ($request->filled('position_id')) {
            $query->where('employees.position_id', $request->position_id);
        }

        // Name filter
        if ($request->filled('search')) {
            $query->where('employees.full_name', 'like', '%' . $request->search . '%');
        }

        // Period filter
        if ($request->filled('period_id')) {
            $periodId = $request->period_id;

            if (str_starts_with($periodId, 'auto_')) {
                $map = [
                    'mingguan' => 'Mingguan',
                    'bulanan' => 'Bulanan',
                    'triwulan' => 'Triwulan',
                    '4_bulanan' => '4 Bulanan',
                    'semesteran' => 'Semester',
                    'tahunan' => 'Tahunan',
                ];
                $key = str_replace('auto_', '', $periodId);
                if (isset($map[$key])) {
                    $query->where('kpi_periods.period_name', 'like', $map[$key] . '%');
                }
            } else {
                $query->where('kpi_assessments.kpi_period_id', $periodId);
            }
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();

            $query->where(function ($subQuery) use ($start, $end) {
                $subQuery->where('kpi_periods.start_date', '>=', $start)
                    ->where('kpi_periods.end_date', '<=', $end);
            });
        }

        $assessments = $query->get();

        // Execute export
        return Excel::download(new KpiReportExport($assessments), 'kpi_report.xlsx');
    }
}