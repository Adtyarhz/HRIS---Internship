<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\OnboardingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingDocumentController extends Controller
{
    /* =======================
     | INDEX
     ======================= */
    public function index()
    {
        $documents = OnboardingDocument::with('division')
            ->latest()
            ->paginate(10);

        return view('onboarding.index', compact('documents'));
    }

    /* =======================
     | CREATE
     ======================= */
    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        return view('onboarding.create', compact('divisions'));
    }

    /* =======================
     | STORE
     ======================= */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'required|mimes:pdf,docx,pptx|max:10240',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $path = $request->file('file')->store('onboarding', 'public');

        OnboardingDocument::create([
            'title'       => $request->title,
            'description' => $request->description,
            'file_path'   => $path,
            'division_id' => $request->division_id,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Dokumen onboarding berhasil ditambahkan');
    }

    /* =======================
     | SHOW
     ======================= */
    public function show(OnboardingDocument $onboardingDocument)
    {
        return view('onboarding.show', compact('onboardingDocument'));
    }

    /* =======================
     | EDIT
     ======================= */
    public function edit(OnboardingDocument $onboardingDocument)
    {
        $divisions = Division::orderBy('name')->get();

        return view('onboarding.edit', compact('onboardingDocument', 'divisions'));
    }

    /* =======================
     | UPDATE
     ======================= */
    public function update(Request $request, OnboardingDocument $onboardingDocument)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'nullable|mimes:pdf,docx,pptx|max:10240',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $data = $request->only([
            'title',
            'description',
            'division_id',
        ]);
        
        $data['is_active']   = $request->boolean('is_active');

        // Jika upload file baru
        if ($request->hasFile('file')) {
            // hapus file lama
            if ($onboardingDocument->file_path && Storage::disk('public')->exists($onboardingDocument->file_path)) {
                Storage::disk('public')->delete($onboardingDocument->file_path);
            }

            $data['file_path'] = $request->file('file')->store('onboarding', 'public');
        }

        $onboardingDocument->update($data);

        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Dokumen onboarding berhasil diperbarui');
    }

    /* =======================
     | DELETE
     ======================= */
    public function destroy(OnboardingDocument $onboardingDocument)
    {
        if ($onboardingDocument->file_path && Storage::disk('public')->exists($onboardingDocument->file_path)) {
            Storage::disk('public')->delete($onboardingDocument->file_path);
        }

        $onboardingDocument->delete();

        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Dokumen onboarding berhasil dihapus');
    }
}
