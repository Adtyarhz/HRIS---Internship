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
 * KpiTemplateController
 * This controller handles the management of KPI Template Master Data.
 * The main logic is that one position can have multiple templates,
 * distinguished by a unique template name for that position.
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
                // Ensure template name is unique for the given position_id
                Rule::unique('kpi_templates')->where(function ($query) use ($request) {
                    return $query->where('position_id', $request->position_id);
                }),
            ],
        ]);

        $template = KpiTemplate::create($validatedData);
        return redirect()->route('kpi-templates.show', $template->id)->with('success', 'Template created successfully. Please add KPI details.');
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
                // Ensure unique template name for the position, ignoring current template
                Rule::unique('kpi_templates')->where(function ($query) use ($request) {
                    return $query->where('position_id', $request->position_id);
                })->ignore($kpiTemplate->id),
            ],
            'is_active' => 'required|boolean',
        ]);

        $kpiTemplate->update($validatedData);

        return redirect()->route('kpi-templates.index')->with('success', 'KPI template updated successfully.');
    }

    public function edit(KpiTemplate $kpiTemplate)
    {
        $positions = Position::orderBy('title')->get();
        return view('kpi.templates.edit', compact('kpiTemplate', 'positions'));
    }

    public function destroy(KpiTemplate $kpiTemplate)
    {
        // Check if the template is used in any assessments
        if ($kpiTemplate->assessments()->exists()) {
            return redirect()->route('kpi-templates.index')->with('error', 'Failed! This template is used in assessments and cannot be deleted.');
        }

        $kpiTemplate->delete();
        return redirect()->route('kpi-templates.index')->with('success', 'KPI template deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for Managing Template Items & Scoring Rules
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
        return back()->with('success', 'KPI item added to template successfully.');
    }

    public function destroyItem(KpiTemplateItem $kpiTemplateItem)
    {
        $kpiTemplateItem->delete();
        return back()->with('success', 'KPI item removed from template successfully.');
    }

    public function storeScoringRule(Request $request, KpiTemplateItem $kpiTemplateItem)
    {
        $validatedData = $request->validate([
            'operator' => 'required|in:<,<=,=,>=,>,between',
            'value1' => 'required|numeric',
            'value2' => 'nullable|numeric|required_if:operator,between',
            'score' => 'required|numeric|min:0',
        ]);

        $kpiTemplateItem->scoringRules()->create($validatedData);
        return back()->with('success', 'Scoring rule added successfully.');
    }

    public function destroyScoringRule(KpiScoringRule $kpiScoringRule)
    {
        $kpiScoringRule->delete();
        return back()->with('success', 'Scoring rule removed successfully.');
    }
}