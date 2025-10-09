<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\WorkExperience;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class WorkExperienceController extends Controller
{
    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $workExperiences = $employee->workExperience()->orderBy('id', 'asc')->get();
        return view('employees.data.work-experience.index', compact('employee', 'workExperiences'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        return view('employees.data.work-experience.create', [
            'employee' => $employee,
            'workExperience' => null
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:20',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if ($request->hasFile('reference_letter_file')) {
                $file = $request->file('reference_letter_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
            }

            if ($request->hasFile('salary_slip_file')) {
                $file = $request->file('salary_slip_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new WorkExperience(), // model dummy untuk request create
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'create'
                );
                if (!$editRequest) {
                    // rollback & hapus file upload jika gagal
                    DB::rollBack();
                    if (!empty($validated['reference_letter_file'])) {
                        Storage::disk('public')->delete($validated['reference_letter_file']);
                    }
                    if (!empty($validated['salary_slip_file'])) {
                        Storage::disk('public')->delete($validated['salary_slip_file']);
                    }
                    return back()->with('error', 'Gagal membuat permintaan pembuatan data pengalaman kerja.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Permintaan penambahan data pengalaman kerja telah dikirim dan menunggu persetujuan.');
            }

            $employee->workExperience()->create($validated);

            DB::commit();
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work Experience was added.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file jika gagal
            if (!empty($validated['reference_letter_file'])) {
                Storage::disk('public')->delete($validated['reference_letter_file']);
            }
            if (!empty($validated['salary_slip_file'])) {
                Storage::disk('public')->delete($validated['salary_slip_file']);
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('employees.data.work-experience.edit', [
            'employee' => $employee,
            'workExperience' => $workExperience
        ]);
    }

    public function update(Request $request, Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:20',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        if ($request->hasFile('reference_letter_file')) {
            $file = $request->file('reference_letter_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
        }

        if ($request->hasFile('salary_slip_file')) {
            $file = $request->file('salary_slip_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $workExperience,
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan perubahan data pengalaman kerja.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
            }

            $workExperience->update($validated);
            DB::commit();

            return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work Experience was Updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        DB::beginTransaction();

        try {
            // 🔹 Jika bukan superadmin/HC → buat request approval
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $workExperience,                            // model yang akan dihapus
                    [],                                         // tidak ada data baru
                    EmployeeEditRequestNotification::class,     // notifikasi
                    ['employee_id' => $employee->id],           // meta data tambahan
                    'delete'                                    // aksi penghapusan
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan penghapusan data pengalaman kerja.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Permintaan penghapusan pengalaman kerja telah dikirim dan menunggu persetujuan.');
            }

            // 🔹 Superadmin/HC langsung hapus
            // Hapus file jika ada
            if ($workExperience->reference_letter_file) {
                Storage::disk('public')->delete($workExperience->reference_letter_file);
            }
            if ($workExperience->salary_slip_file) {
                Storage::disk('public')->delete($workExperience->salary_slip_file);
            }

            $workExperience->delete();
            DB::commit();

            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work Experience was Deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function authorizeAccess(Employee $employee)
    {
        $user = Auth::user();

        if (in_array($user->role, ['hc', 'superadmin'])) {
            return true;
        }

        if ($user->employee && $user->employee->id === $employee->id) {
            return true;
        }

        abort(403, 'Unauthorized action.');
    }
}
