<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Models\CareerHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Automatically update status if separation_date has passed
        Employee::whereNotNull('separation_date')
            ->where('separation_date', '<', Carbon::today())
            ->where('status', '!=', 'Tidak Aktif')
            ->update(['status' => 'Tidak Aktif']);

        $user = Auth::user();

        // If superadmin or HC, view all active employees
        if (in_array($user->role, ['superadmin', 'hc'])) {
            $query = Employee::where('status', 'Aktif');
        } else {
            // Jika user biasa, hanya lihat data karyawan miliknya (user_id)
            $query = Employee::where('status', 'Aktif')
                ->where('user_id', $user->id);
        }

        // Apply search filter if provided
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        // Fetch additional data for filters
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get(); // except N/A
        $positions = Position::orderBy('title')->get();

        // Pagination
        $employees = $query->latest()->paginate(9)->withQueryString();

        return view('employees.data.index', compact('employees', 'divisions', 'positions'));
    }

    public function indexCareer(Request $request)
    {
        $user = Auth::user();

        // Check role: superadmin, hc, or direksi vs regular user
        if (in_array($user->role, ['superadmin', 'hc', 'direksi'])) {
            $query = Employee::where('status', 'Aktif');
        } else {
            $query = Employee::where('status', 'Aktif')
                ->where('user_id', $user->id);
        }

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        // Additional filters
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        $employees = $query->latest()->paginate(9)->withQueryString();

        return view('career-path.indexCareer', compact('employees', 'divisions', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get(); // except N/A
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')->orderBy('name')->get();
        return view('employees.data.create', compact('divisions', 'positions', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'nik' => 'required|string|size:16|unique:employees,nik|regex:/^[0-9]+$/',
            'nip' => 'nullable|string|max:20|unique:employees,nip|regex:/^[0-9]+$/',
            'npwp' => 'nullable|string|max:20|unique:employees,npwp|regex:/^[0-9]+$/',
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'string', 'max:20', 'unique:employees,phone_number', 'regex:/^\+?[0-9]{8,20}$/'],
            'email' => 'required|email|max:100|unique:employees,email',
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'office' => ['nullable', Rule::in(['Kantor Pusat', 'Kantor Cabang'])],
            'hire_date' => 'required|date',
            'separation_date' => 'nullable|date|after_or_equal:hire_date',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|unique:employees,user_id|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('cv_file')) {
                $file = $request->file('cv_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validateData['cv_file'] = $file->storeAs('cv', $filename, 'public');
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validateData['photo'] = $file->storeAs('photo', $filename, 'public');
            }

            // Make sure Division_id has default 'N/A' if empty/null
            if (empty($validatedData['division_id'])) {
                // Find or create default division 'N/A'
                $defaultDivision = Division::firstOrCreate(
                    ['name' => 'N/A'],
                    ['description' => 'Default division for undefined employees'] // optional column
                );
                $validatedData['division_id'] = $defaultDivision->id;
            }

            $employee = Employee::create($validateData);

            // Create initial CareerHistory entry
            if ($validateData['position_id']) {
                CareerHistory::create([
                    'employee_id' => $employee->id,
                    'position_id' => $validateData['position_id'],
                    'division_id' => $validateData['division_id'],
                    'employee_type' => $validateData['employee_type'],
                    'start_date' => $validateData['hire_date'],
                    'end_date' => null,
                    'type' => 'Awal Masuk',
                    'notes' => '',
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee data added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if operation fails
            if (isset($validateData['cv_file'])) {
                Storage::disk('public')->delete($validateData['cv_file']);
            }
            if (isset($validateData['photo'])) {
                Storage::disk('public')->delete($validateData['photo']);
            }

            return back()->with('error', 'Error occurred while saving data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $age = null;
        if ($employee->birth_date) {
            $age = Carbon::parse($employee->birth_date)->age;
        }
        $healthRecord = $employee->healthRecord;
        $educationHistories = $employee->educationHistory;
        $dependents = $employee->familyDependents;
        $certifications = $employee->certifications;
        $insurances = $employee->insurance;
        $workExperiences = $employee->workExperience;
        $trainingHistories = $employee->trainingHistories;
        return view('employees.data.show', compact('employee', 'age', 'healthRecord', 'educationHistories', 'dependents', 'certifications', 'insurances', 'workExperiences', 'trainingHistories'));
    }

    /**
     * Display the specified resource for career path details.
     */
    public function showCareer(Employee $employee)
    {
        $user = Auth::user();

        // Only superadmin, hc, direksi, or data owner can view
        if (!in_array($user->role, ['superadmin', 'hc', 'direksi']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have access to this data.');
        }

        $careerHistories = $employee->careerHistories()->with(['position', 'division'])->get();
        $careerProjection = $employee->careerProjection()->with(['projectedPosition', 'creator'])->first();

        return view('career-path.showCareer', compact('employee', 'careerHistories', 'careerProjection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $user = Auth::user();

        // Non-superadmin/hc can only edit their own data
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have access to edit this data.');
        }

        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get(); // except N/A
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')->orWhere('id', $employee->user_id)->orderBy('name')->get();

        return view('employees.data.edit', compact('employee', 'divisions', 'positions', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $user = Auth::user();

        // Non-superadmin/hc can only update their own data
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have access to update this data.');
        }

        // Validation
        $validatedData = $request->validate([
            'nik' => ['required', 'string', 'size:16', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'npwp' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^\+?[0-9]{8,20}$/'],
            'email' => ['required', 'email', 'max:100', Rule::unique('employees')->ignore($employee->id)],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'office' => ['nullable', Rule::in(['Kantor Pusat', 'Kantor Cabang'])],
            'hire_date' => 'required|date',
            'separation_date' => 'nullable|date|after_or_equal:hire_date',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees')->ignore($employee->id)],
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads (if any), but store temporarily, don't update DB directly if not superadmin/hc
            if ($request->hasFile('cv_file')) {
                $file = $request->file('cv_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validatedData['cv_file'] = $file->storeAs('cv', $filename, 'public');
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validatedData['photo'] = $file->storeAs('photo', $filename, 'public');
            }

            // If not superadmin/hc → create edit request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $employee,
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    [
                        'employee_id' => $employee->id,
                    ]
                );
                if (!$editRequest) {
                    return back()->with('error', 'Failed to create data update request.');
                }
                DB::commit();
                return redirect()->route('employees.show', $employee->id)
                    ->with('info', 'Data update request has been sent and is awaiting approval.');
            }

            // If superadmin/hc → directly update the database
            if ($request->hasFile('cv_file') && $employee->cv_file) {
                Storage::disk('public')->delete($employee->cv_file);
            }

            if ($request->hasFile('photo') && $employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }

            // Store old data before update
            $oldPosition = $employee->position;
            $oldDivision = $employee->division_id;
            $oldType = $employee->employee_type;

            $employee->update($validatedData);

            // Make sure Division_id has default 'N/A' if empty/null
            if (empty($validatedData['division_id'])) {
                // Find or create default division 'N/A'
                $defaultDivision = Division::firstOrCreate(
                    ['name' => 'N/A'],
                    ['description' => 'Default division for undefined employees'] // optional column
                );
                $validatedData['division_id'] = $defaultDivision->id;
            }

            // Check for career changes
            $newPosition = Position::find($validatedData['position_id'] ?? $employee->position_id);
            $newDivision = $validatedData['division_id'] ?? $employee->division_id;
            $newType = $validatedData['employee_type'] ?? $employee->employee_type;

            $careerType = null;

            if (!$oldPosition && $newPosition) {
                $careerType = 'Awal Masuk';
            } elseif ($oldPosition && $newPosition && $oldPosition->id !== $newPosition->id) {
                if ($newPosition->depth < $oldPosition->depth) {
                    $careerType = 'Promosi';
                } elseif ($newPosition->depth > $oldPosition->depth) {
                    $careerType = 'Demosi';
                } else {
                    $careerType = 'Mutasi';
                }
            } elseif ($oldDivision != $newDivision) {
                $careerType = 'Mutasi';
            } elseif ($oldType != $newType) {
                $careerType = 'Mutasi';
            }

            // If there are changes in position/division/employee type
            if ($careerType) {
                // Close active career history
                $activeCareer = CareerHistory::where('employee_id', $employee->id)
                    ->whereNull('end_date')
                    ->first();

                if ($activeCareer) {
                    $activeCareer->update([
                        'end_date' => Carbon::today(),
                    ]);
                }

                // Add new career history
                CareerHistory::create([
                    'employee_id' => $employee->id,
                    'position_id' => $newPosition ? $newPosition->id : null,
                    'division_id' => $newDivision,
                    'employee_type' => $newType,
                    'start_date' => Carbon::today(),
                    'end_date' => null,
                    'type' => $careerType,
                    'notes' => '',
                ]);
            }

            DB::commit();
            return redirect()->route('employees.show', $employee->id)->with('success', 'Employee data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred while updating data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();

            if ($employee->cv_file) {
                Storage::delete('public/cv/' . $employee->cv_file);
            }

            if ($employee->photo) {
                Storage::delete('public/photo/' . $employee->photo);
            }

            $employee->delete();
            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('employees.index')->with('error', 'Failed to delete employee data. It may be linked to other data.');
        }
    }

    public function deactivate(Employee $employee): RedirectResponse
    {
        if ($employee->status !== 'Tidak Aktif') {
            $employee->status = 'Tidak Aktif';
            $employee->separation_date = now();
            $employee->save();

            return redirect()->back()->with('success', 'Employee status changed to Inactive successfully.');
        }

        return redirect()->back()->with('info', 'Employee is already Inactive.');
    }

    public function editAddress(Employee $employee)
    {
        $user = Auth::user();

        // If not HC or Superadmin, only allow access to own data
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized access to address data.');
            }
        }

        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get(); // except N/A
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')
            ->orWhere('id', $employee->user_id)
            ->orderBy('name')
            ->get();

        return view('employees.data.edit', compact('employee', 'divisions', 'positions', 'users'));
    }
}