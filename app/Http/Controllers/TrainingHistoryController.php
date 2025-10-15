<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingMaterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class TrainingHistoryController extends Controller
{
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = Auth::user();

        // If not HC or Superadmin, only allow access to own data
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'You do not have permission to access this data.');
            }
        }
    }

    public function index(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $trainingHistories = $employee->trainingHistories()->with('trainingMaterials')->latest('start_date')->get();
        return view('employees.training-histories.index', compact('employee', 'trainingHistories'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        return view('employees.training-histories.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $validatedData = $request->validate([
            'training_name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cost' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:50',
            'material_files' => 'nullable|array|max:10',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240'
        ]);

        $user = Auth::user();
        DB::beginTransaction();
        try {
            $storedFiles = [];

            // Upload training material files (if any)
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    if ($materialFile->isValid()) {
                        $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                            . '.' . $materialFile->getClientOriginalExtension();
                        $materialFile->storeAs('training_materials', $materialFileName, 'public');
                        $storedFiles[] = 'training_materials/' . $materialFileName;
                    } else {
                        throw new \Exception('One or more training material files failed to upload.');
                    }
                }
            }

            // If not superadmin/hc, create an edit request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $validatedData['material_files_uploaded'] = $storedFiles;

                $notifier = new RequestNotifierService();
                $editRequest = $notifier->createEditRequest(
                    new TrainingHistory(),
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create training history request.');
                }

                DB::commit();
                return redirect()->route('employees.training-histories.index', $employee->id)
                    ->with('info', 'Training history addition request has been sent and is awaiting approval.');
            }

            // Superadmin/HC directly save
            $trainingHistory = $employee->trainingHistories()->create($validatedData);
            foreach ($storedFiles as $filename) {
                $trainingHistory->trainingMaterials()->create(['file_path' => $filename]);
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                ->with('success', 'Training history added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file yang mungkin sudah ter-upload sebagian
            if (!empty($storedFiles)) {
                foreach ($storedFiles as $file) {
                    Storage::disk('public')->delete($file);
                }
            }

            return back()->with('error', 'Failed to save training history: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $trainingHistory->load('trainingMaterials');
        return view('employees.training-histories.edit', compact('employee', 'trainingHistory'));
    }

    public function update(Request $request, Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $validatedData = $request->validate([
            'training_name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cost' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:50',
            'material_files' => 'nullable|array',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240',
            'delete_materials' => 'nullable|array',
        ]);

        $user = Auth::user();
        DB::beginTransaction();
        try {
            $storedFiles = [];
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('training_materials', $materialFileName, 'public');
                    $storedFiles[] = $materialFileName;
                }
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $originalData = $trainingHistory->only(array_keys($validatedData));

                $validatedData['material_files_uploaded'] = $storedFiles;
                $validatedData['delete_materials'] = $validatedData['delete_materials'] ?? [];

                $editRequest = $notifier->createEditRequest(
                    $trainingHistory,
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    [
                        'employee_id' => $employee->id,
                        'method' => 'update',
                    ]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create training history update request.');
                }

                DB::commit();
                return redirect()->route('employees.training-histories.index', $employee->id)
                    ->with('info', 'Training history update request has been sent and is awaiting approval.');
            }

            $trainingHistory->update($validatedData);

            // Delete selected files
            if (!empty($validatedData['delete_materials'])) {
                foreach ($validatedData['delete_materials'] as $materialId) {
                    $material = TrainingMaterial::where('training_history_id', $trainingHistory->id)
                        ->where('id', $materialId)
                        ->first();
                    if ($material) {
                        Storage::disk('public')->delete('training_materials/' . $material->file_path);
                        $material->delete();
                    }
                }
            }

            // Upload new files
            foreach ($storedFiles as $filename) {
                $trainingHistory->trainingMaterials()->create(['file_path' => $filename]);
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                ->with('success', 'Training history updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update training history: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = Auth::user();
        DB::beginTransaction();

        try {
            // If not superadmin/hc, create a delete approval request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $trainingHistory,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create training history deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.training-histories.index', $employee->id)
                    ->with('info', 'Training history deletion request has been sent and is awaiting approval.');
            }

            // If superadmin/hc, directly delete data and files
            foreach ($trainingHistory->trainingMaterials as $material) {
                Storage::disk('public')->delete('training_materials/' . $material->file_path);
            }

            $trainingHistory->delete();

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                ->with('success', 'Training history deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete training history: ' . $e->getMessage());
        }
    }

    public function destroyMaterial(Employee $employee, TrainingHistory $trainingHistory, TrainingMaterial $material)
    {
        $this->authorizeEmployeeAccess($employee);

        if ($trainingHistory->employee_id !== $employee->id || $material->training_history_id !== $trainingHistory->id) {
            abort(403, 'Material file is not associated with this training or employee.');
        }

        DB::beginTransaction();
        try {
            if ($material->file_path) {
                Storage::delete('public/training_materials/' . $material->file_path);
            }
            $material->delete();

            DB::commit();
            return back()->with('success', 'Material file deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete material file: ' . $e->getMessage());
        }
    }
}