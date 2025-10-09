<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Insurance;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class InsuranceController extends Controller
{
    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $insurances = $employee->insurance()->orderBy('id', 'asc')->get();

        return view('employees.insurance.index', compact('employee', 'insurances'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        return view('employees.insurance.create', [
            'employee' => $employee,
            'insurance' => null
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number',
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new Insurance(),                           // model dummy untuk referensi tipe
                    $validated,                                 // data yang diusulkan
                    EmployeeEditRequestNotification::class,     // notifikasi ke HC
                    ['employee_id' => $employee->id],           // konteks tambahan
                    'create'                                    // metode perubahan
                );

                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['insurance_file'])) {
                        Storage::disk('public')->delete($validated['insurance_file']);
                    }
                    return back()->with('error', 'Gagal membuat permintaan penambahan data asuransi.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Permintaan penambahan data telah dikirim dan menunggu persetujuan.');
            }

            $employee->insurance()->create($validated);
            DB::commit();

            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was added.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($validated['insurance_file'])) {
                Storage::disk('public')->delete($validated['insurance_file']);
            }
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('employees.insurance.edit', [
            'employee' => $employee,
            'insurance' => $insurance
        ]);
    }

    public function update(Request $request, Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number,' . $insurance->id,
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            // ✅ Upload file baru (jika ada)
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $file->getClientOriginalExtension();
                $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            // ✅ Flow Approval untuk non-HC / non-superadmin
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $insurance,
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'update'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['insurance_file'])) {
                        Storage::disk('public')->delete($validated['insurance_file']);
                    }
                    return back()->with('error', 'Gagal membuat permintaan perubahan data asuransi.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Permintaan perubahan data asuransi telah dikirim dan menunggu persetujuan.');
            }

            // ✅ Jika HC / Superadmin → langsung update
            // Hapus file lama jika diganti
            if (isset($validated['insurance_file']) && $insurance->insurance_file) {
                Storage::disk('public')->delete($insurance->insurance_file);
            }

            $insurance->update($validated);
            DB::commit();

            return redirect()->route('employees.insurance.index', $employee)
                ->with('success', 'Data asuransi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($validated['insurance_file'])) {
                Storage::disk('public')->delete($validated['insurance_file']);
            }

            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        DB::beginTransaction();

        try {
            // ✅ Flow Approval untuk non-HC / non-superadmin
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $insurance,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan penghapusan data asuransi.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Permintaan penghapusan data asuransi telah dikirim dan menunggu persetujuan.');
            }

            // ✅ HC / Superadmin → langsung hapus
            if ($insurance->insurance_file) {
                Storage::disk('public')->delete($insurance->insurance_file);
            }

            $insurance->delete();

            DB::commit();
            return redirect()->route('employees.insurance.index', $employee)
                ->with('success', 'Data asuransi berhasil dihapus.');
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
