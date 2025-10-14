<?php

namespace App\Http\Controllers;

use App\Models\KpiIndicator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiIndicatorController extends Controller
{
    public function index()
    {
        $kpiIndicators = KpiIndicator::latest()->paginate(10);
        return view('kpi.indicators.index', compact('kpiIndicators'));
    }

    public function create()
    {
        return view('kpi.indicators.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'indicator_name' => 'required|string|max:255|unique:kpi_indicators,indicator_name',
            'description' => 'nullable|string',
            'measurement_unit' => 'required|string|max:50',
            'higher_is_better' => 'required|boolean',
        ]);

        KpiIndicator::create($validatedData);
        return redirect()->route('kpi-indicators.index')->with('success', 'KPI indicator created successfully.');
    }

    public function edit(KpiIndicator $kpiIndicator)
    {
        return view('kpi.indicators.edit', compact('kpiIndicator'));
    }

    public function update(Request $request, KpiIndicator $kpiIndicator)
    {
        $validatedData = $request->validate([
            'indicator_name' => ['required', 'string', 'max:255', Rule::unique('kpi_indicators')->ignore($kpiIndicator->id)],
            'description' => 'nullable|string',
            'measurement_unit' => 'required|string|max:50',
            'higher_is_better' => 'required|boolean',
        ]);

        $kpiIndicator->update($validatedData);
        return redirect()->route('kpi-indicators.index')->with('success', 'KPI indicator updated successfully.');
    }

    public function destroy(KpiIndicator $kpiIndicator)
    {
        // Check if the indicator is used in any templates
        if ($kpiIndicator->templates()->exists()) {
            return redirect()->route('kpi-indicators.index')->with('error', 'Failed! This KPI indicator is used in templates and cannot be deleted.');
        }

        $kpiIndicator->delete();
        return redirect()->route('kpi-indicators.index')->with('success', 'KPI indicator deleted successfully.');
    }
}