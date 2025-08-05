<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicantController extends Controller
{
   public function index(Request $request)
{
    $search = $request->input('search');
    $sortBy = $request->input('sort', 'created_at');
    $direction = $request->input('direction', 'desc');

    $allowedSorts = ['id', 'applied_position'];
    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'id';
    }

    $user = auth()->user();

    $applicants = Applicant::with('division')
        ->when($search, function ($query) use ($search) {
            $query->where('full_name', 'like', '%' . $search . '%');
        })
        ->when($user->role === 'section_head', function ($query) use ($user) {
            // Section Head hanya melihat berdasarkan divisi dirinya
            $query->where('division_id', optional($user->employee)->division_id);
        })
        ->orderBy($sortBy, $direction)
        ->paginate(10)
        ->withQueryString();

    return view('applicants.index', compact('applicants', 'search', 'sortBy', 'direction'));
}

    public function create()
    {
        $divisions = Division::all();
        return view('applicants.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email',
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'required|file|mimes:pdf,doc,docx',
            'applied_position' => 'required',
            'last_education' => 'required|string',
            'origin' => 'required|string',
            'gpa_score' => 'required|numeric',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        if ($request->hasFile('resume_file')) {
    $file = $request->file('resume_file');
    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
    $validated['resume_file'] = $file->storeAs('resumes', $filename, 'public');
        }

        Applicant::create($validated);

        return redirect()->route('applicants.index')->with('success', 'Applicant added successfully!');
    }

    public function edit(Applicant $applicant)
    {
        $divisions = Division::all();
        return view('applicants.edit', compact('applicant', 'divisions'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email,' . $applicant->id,
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx',
            'applied_position' => 'required',
            'last_education' => 'required|string',
            'origin' => 'required|string',
            'gpa_score' => 'required|numeric',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

       if ($request->hasFile('resume_file')) {
    $file = $request->file('resume_file');
    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
    $validated['resume_file'] = $file->storeAs('resumes', $filename, 'public');
}


        $applicant->update($validated);

        return redirect()->route('applicants.index')->with('success', 'Applicant updated successfully!');
    }

    public function show(Applicant $applicant)
    {
        return view('applicants.show', compact('applicant'));
    }

    public function destroy(Applicant $applicant)
    {
        $applicant->delete();
        return redirect()->route('applicants.index')->with('success', 'Applicant deleted successfully!');
    }
}
