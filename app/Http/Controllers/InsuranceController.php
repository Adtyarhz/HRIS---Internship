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
\Log::info('Insurance request created', ['data' => $validated]);
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                EmployeeEditRequest::create([
                    'employee_id'   => $employee->id,
                    'method'        => 'create',
                    'model'         => Insurance::class,
                    'model_id'      => null,
                    'original_data' => null,
                    'changed_data'  => $validated,
                    'status'        => 'waiting',
                    'requested_by'  => $user->id,
                    'requested_at'  => now(),
                ]);

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
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                EmployeeEditRequest::create([
                    'employee_id'   => $employee->id,
                    'method'        => 'update',
                    'model'         => Insurance::class,
                    'model_id'      => $insurance->id,
                    'original_data' => $insurance->only(array_keys($validated)),
                    'changed_data'  => $validated,
                    'status'        => 'waiting',
                    'requested_by'  => $user->id,
                    'requested_at'  => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                                 ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
            }

            $insurance->update($validated);
            DB::commit();

            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $insurance->delete();

        return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was deleted.');
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
