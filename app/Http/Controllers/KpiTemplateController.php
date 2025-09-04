<?php

namespace App\Http\Controllers;

use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use App\Models\KpiScoringRule;
use App\Models\KpiIndicator;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * ======================================================================
 * KpiTemplateController
 * ======================================================================
 * Controller ini bertanggung jawab penuh atas manajemen Master Data Template KPI.
 * Logika utamanya adalah satu jabatan bisa memiliki lebih dari satu template,
 * yang dibedakan berdasarkan nama template yang unik untuk jabatan tersebut.
 */
class KpiTemplateController extends Controller
{
    public function index()
    {
        $kpiTemplates = KpiTemplate::with('position')->latest()->paginate(10);
        return view('kpi.templates.index', compact('kpiTemplates'));
    }

    public function create()
    {
        $positions = Position::orderBy('title')->get();
        return view('kpi.templates.create', compact('positions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'template_name' => [
                'required',
                'string',
                'max:255',
                // Validasi: Nama template harus unik untuk setiap position_id.
                Rule::unique('kpi_templates')->where(function ($query) use ($request) {
                    return $query->where('position_id', $request->position_id);
                }),
            ],
            // 'is_active' => 'required|boolean',
        ]);

        $template = KpiTemplate::create($validatedData);
        return redirect()->route('kpi-templates.show', $template->id)->with('success', 'Templat berhasil dibuat. Silakan isi detail KPI.');
    }

    public function show(KpiTemplate $kpiTemplate)
    {
        $kpiTemplate->load('templateItems.indicator', 'templateItems.scoringRules');
        $existingIndicatorIds = $kpiTemplate->templateItems->pluck('kpi_indicator_id');
        $availableIndicators = KpiIndicator::whereNotIn('id', $existingIndicatorIds)->orderBy('indicator_name')->get();
        return view('kpi.templates.show', compact('kpiTemplate', 'availableIndicators'));
    }

    public function update(Request $request, KpiTemplate $kpiTemplate)
    {
        $validatedData = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'template_name' => [
                'required',
                'string',
                'max:255',
                // Validasi unik, tapi abaikan ID template yang sedang diedit.
                Rule::unique('kpi_templates')->where(function ($query) use ($request) {
                    return $query->where('position_id', $request->position_id);
                })->ignore($kpiTemplate->id),
            ],
            'is_active' => 'required|boolean',
        ]);

        $kpiTemplate->update($validatedData);

        return redirect()->route('kpi-templates.index')->with('success', 'Templat KPI berhasil diperbarui.');
    }

    public function edit(KpiTemplate $kpiTemplate)
    {
        $positions = Position::orderBy('title')->get();
        return view('kpi.templates.edit', compact('kpiTemplate', 'positions'));
    }

    public function destroy(KpiTemplate $kpiTemplate)
    {
        // Sebaiknya tambahkan pengecekan apakah template pernah digunakan di assessment
        // sebelum menghapus untuk menjaga integritas data.
        $kpiTemplate->delete();
        return redirect()->route('kpi-templates.index')->with('success', 'Templat KPI berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | Metode untuk Manajemen Item & Aturan Skor di dalam Template
    |--------------------------------------------------------------------------
    */

    public function storeItem(Request $request, KpiTemplate $kpiTemplate)
    {
        $validatedData = $request->validate([
            'kpi_indicator_id' => 'required|exists:kpi_indicators,id',
            'type' => 'required|integer|in:1,2,3',
            'weight' => 'required|numeric|min:0|max:100',
            'default_target' => 'required|string|max:255',
        ]);
        $kpiTemplate->templateItems()->create($validatedData);
        return back()->with('success', 'Item KPI berhasil ditambahkan ke templat.');
    }

    public function destroyItem(KpiTemplateItem $kpiTemplateItem)
    {
        $kpiTemplateItem->delete();
        return back()->with('success', 'Item KPI berhasil dihapus dari templat.');
    }

    // Metode untuk mengelola aturan skoring
    public function storeScoringRule(Request $request, KpiTemplateItem $kpiTemplateItem)
    {
        $validatedData = $request->validate([
            'operator' => 'required|in:<,<=,=,>=,>,between',
            'value1' => 'required|numeric',
            'value2' => 'nullable|numeric|required_if:operator,between',
            'score' => 'required|numeric|min:0',
        ]);
        $kpiTemplateItem->scoringRules()->create($validatedData);
        return back()->with('success', 'Aturan skoring berhasil ditambahkan.');
    }

    public function destroyScoringRule(KpiScoringRule $kpiScoringRule)
    {
        $kpiScoringRule->delete();
        return back()->with('success', 'Aturan skoring berhasil dihapus.');
    }
}