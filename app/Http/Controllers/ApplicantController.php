<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\NewApplicantNotification;

class ApplicantController extends Controller
{
   public function index(Request $request)
{
    $search = $request->input('search');
    $sortBy = $request->input('sort', 'created_at');
    $direction = $request->input('direction', 'desc');

    $allowedSorts = ['id', 'position', 'created_at'];
    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'id';
    }

    $user = auth()->user();
    $divisionId = optional($user->employee)->division_id;

    $applicants = Applicant::with(['division', 'position'])
        // 🔍 Pencarian nama
        ->when($search, function ($query) use ($search) {
            $query->where('full_name', 'like', '%' . $search . '%');
        })
        // 👥 Filter berdasarkan role & divisi
        ->when(in_array($user->role, ['manager', 'section_head']), function ($query) use ($divisionId) {
            $query->where('division_id', $divisionId);
        })
        // ⚙️ Sorting
        ->when($sortBy === 'position', function ($query) use ($direction) {
            $query->leftJoin('positions', 'positions.id', '=', 'applicants.applied_position')
                ->orderBy('positions.title', $direction)
                ->select('applicants.*');
        }, function ($query) use ($sortBy, $direction) {
            $query->orderBy("applicants.$sortBy", $direction);
        })
        ->paginate(10)
        ->withQueryString();

    return view('applicants.index', compact('applicants', 'search', 'sortBy', 'direction'));
}

    public function create()
    {
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
        $positions = Position::all();
        return view('applicants.create', compact('divisions', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email',
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'required|file|mimes:pdf,doc,docx',
            'applied_position' => 'required|exists:positions,id',
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

        $applicant = Applicant::create($validated);

        $managers = User::whereHas('employee', function($q) use ($applicant) {
            $q->where('division_id', $applicant->division_id);
        })->where('role', 'manager')->get();

        if ($managers->isEmpty()) {
            $divisionHeads = User::whereHas('employee', function($q) use ($applicant) {
                $q->where('division_id', $applicant->division_id);
            })->where('role', 'section_head')->get();
        } else {
            $divisionHeads = $managers;
        }

        $hcUsers = User::admins()->get();
        foreach ($hcUsers->merge($divisionHeads) as $user) {
            $user->notify(new NewApplicantNotification($applicant));
        }

        return redirect()->route('applicants.index')->with('success', 'Applicant added successfully!');
    }

    public function edit(Applicant $applicant)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
        $positions = Position::all();
        return view('applicants.edit', compact('applicant', 'divisions', 'positions'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email,' . $applicant->id,
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx',
            'applied_position' => 'required|exists:positions,id',
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

   public function show($id)
{
    // Ambil applicant + relasi division (jika ada)
    $applicant = Applicant::with('division')->findOrFail($id);
    $offeringLetter = $applicant->offeringLetter;
    $user = auth()->user();

    // 🔒 Hanya role tertentu yang boleh melihat semua data
    if (!in_array($user->role, ['superadmin', 'hc'])) {

        // Ambil divisi dari user login
        $userDivisionId = optional($user->employee)->division_id;

        // Ambil divisi dari applicant
        $applicantDivisionId = $applicant->division_id;

        // Manager & section head hanya bisa lihat applicant di divisinya
        if (in_array($user->role, ['manager', 'section_head'])) {
            if ($userDivisionId !== $applicantDivisionId) {
                abort(403, 'Anda tidak memiliki izin untuk melihat pelamar dari divisi lain.');
            }
        } else {
            // Role lain tidak boleh sama sekali
            abort(403);
        }
    }

    // 🔍 Cek apakah applicant sudah jadi employee
    $isConverted = \App\Models\Employee::where('full_name', $applicant->full_name)
        ->where('email', $applicant->email)
        ->where('phone_number', $applicant->phone)
        ->exists();

    return view('applicants.show', compact('applicant', 'offeringLetter', 'isConverted'));
}

    public function destroy(Applicant $applicant)
    {   
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $applicant->delete();
        return redirect()->route('applicants.index')->with('success', 'Applicant deleted successfully!');
    }

    public function convertToEmployee($id)
    {   
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $applicant = Applicant::with('position')->findOrFail($id);

        $offeringLetter = $applicant->recruitmentProgresses()
            ->where('stage', 'offering_letter')
            ->first();

        if (!$offeringLetter || $offeringLetter->offering_status !== 'accepted') {
            return redirect()->back()->with('error', 'Applicant cannot be converted to employee because the offering letter is not accepted.');
        }

        if ($applicant->employee) {
            return redirect()->back()->with('info', 'This applicant is already registered as an employee.');
        }

        $nik = 'EMP' . date('Y') . str_pad($applicant->id, 4, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'applicant_id'  => $applicant->id,
            'nik'           => $nik,
            'full_name'     => $applicant->full_name,
            'email'         => $applicant->email,
            'position_id'   => $applicant->applied_position,
            'contract_type' => $offeringLetter->contract_type ?? null,
            'start_date'    => now(),
        ]);

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Applicant has been successfully converted to employee.');
    }
}
