<?php

namespace App\Http\Controllers;

use App\Models\KpiIndicator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Services\ApprovalWorkflowService;

class KpiIndicatorController extends Controller
{
    /**
     * Memastikan hanya role tertentu yang bisa mengakses.
     */
    private function authorizeAccess()
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'hc'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index()
    {
        $kpiIndicators = KpiIndicator::latest()->paginate(10);
        return view('kpi.indicators.index', compact('kpiIndicators'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('kpi.indicators.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validatedData = $request->validate([
            'indicator_name' => 'required|string|max:255|unique:kpi_indicators,indicator_name',
            'description' => 'nullable|string',
            'measurement_unit' => 'required|string|max:50',
            'higher_is_better' => 'required|boolean',
        ]);

        $user = Auth::user();

        // ✅ Approval Logic (HC)
        if ($user && $user->role === 'hc') {
            $tempModel = new KpiIndicator($validatedData);

            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');

            return redirect()->route('kpi-indicators.index')
                ->with('success', 'Permintaan pembuatan Indikator KPI telah dikirim untuk approval.');
        }

        // ✅ Langsung buat untuk superadmin
        KpiIndicator::create($validatedData);
        return redirect()->route('kpi-indicators.index')
            ->with('success', 'KPI indicator created successfully.');
    }

    public function edit(KpiIndicator $kpiIndicator)
    {
        $this->authorizeAccess();
        return view('kpi.indicators.edit', compact('kpiIndicator'));
    }

    public function update(Request $request, KpiIndicator $kpiIndicator)
    {
        $this->authorizeAccess();

        $validatedData = $request->validate([
            'indicator_name' => ['required', 'string', 'max:255', Rule::unique('kpi_indicators')->ignore($kpiIndicator->id)],
            'description' => 'nullable|string',
            'measurement_unit' => 'required|string|max:50',
            'higher_is_better' => 'required|boolean',
        ]);

        $user = Auth::user();

        // ✅ Approval Logic (HC)
        if ($user && $user->role === 'hc') {
            $oldData = $kpiIndicator->toArray();
            $tempModel = clone $kpiIndicator;
            $tempModel->fill($validatedData);

            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update', $oldData);

            return redirect()->route('kpi-indicators.index')
                ->with('success', 'Permintaan perubahan Indikator KPI telah dikirim untuk approval.');
        }

        // ✅ Langsung update untuk superadmin
        $kpiIndicator->update($validatedData);
        return redirect()->route('kpi-indicators.index')
            ->with('success', 'KPI indicator updated successfully.');
    }

    public function destroy(KpiIndicator $kpiIndicator)
    {
        $this->authorizeAccess();

        // Pastikan indikator tidak digunakan di template KPI
        if ($kpiIndicator->templates()->exists()) {
            return redirect()->route('kpi-indicators.index')
                ->with('error', 'Gagal! Indikator KPI ini sedang digunakan dalam template dan tidak dapat dihapus.');
        }

        $user = Auth::user();

        // ✅ Approval Logic (HC)
        if ($user && $user->role === 'hc') {
            $oldData = $kpiIndicator->toArray();

            ApprovalWorkflowService::captureModelChange($user, $kpiIndicator, 'delete', $oldData);

            return redirect()->route('kpi-indicators.index')
                ->with('success', 'Permintaan penghapusan Indikator KPI telah dikirim untuk approval.');
        }

        // ✅ Langsung hapus untuk superadmin
        $kpiIndicator->delete();
        return redirect()->route('kpi-indicators.index')
            ->with('success', 'KPI indicator deleted successfully.');
    }
}
