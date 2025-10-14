<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RequestNotifierService
{
    /**
     * Create a data change request (create/update/delete) and send notification to HC/Superadmin.
     *
     * @param  Model   $model
     * @param  array   $validatedData
     * @param  string  $notificationClass
     * @param  array   $extraData
     * @param  string|null $operation  // create | update | delete | null (auto-detect)
     * @return \App\Models\EmployeeEditRequest|false
     */
    public function createEditRequest(
        Model $model,
        array $validatedData,
        string $notificationClass,
        array $extraData = [],
        ?string $operation = null
    ) {
        try {
            $user = Auth::user();
            $modelName = class_basename($model);

            // Find the appropriate EditRequest class (e.g., EmployeeEditRequest)
            $editRequestClass = "App\\Models\\{$modelName}EditRequest";
            if (!class_exists($editRequestClass)) {
                // Fallback: use default EmployeeEditRequest
                $editRequestClass = "App\\Models\\EmployeeEditRequest";
            }

            // Auto-detect method if not provided
            $method = $operation ?? $this->detectMethod($model, $validatedData);

            // Original data only for update/delete
            $originalData = null;
            if ($method === 'update' && $model->exists) {
                $originalData = $model->only(array_keys($validatedData));
            }

            // Create data change request
            $editRequest = $editRequestClass::create(array_merge([
                'employee_id'   => $extraData['employee_id'] ?? ($model->employee_id ?? null),
                'method'        => $method,
                'model'         => get_class($model),
                'model_id'      => $model->id ?? null,
                'original_data' => $originalData,
                'changed_data'  => $validatedData,
                'status'        => 'waiting',
                'requested_by'  => $user->id,
                'requested_at'  => now(),
            ], $extraData));

            // Send notification to HC & Superadmin
            $admins = User::whereIn('role', ['hc', 'superadmin'])
                ->whereKeyNot($user->id)
                ->get();

            if ($admins->isEmpty()) {
                Log::warning("No HC/Superadmin recipients found for {$modelName} notification.");
                return $editRequest;
            }

            foreach ($admins as $admin) {
                $employeeGender = $user->employee->gender;

                $admin->notify(new $notificationClass(
                    $user->name ?? 'Karyawan',
                    $editRequest->id,
                    $employeeGender
                ));
            }

            Log::info("{$method} request notification sent successfully.", [
                'model' => $modelName,
                'request_id' => $editRequest->id,
                'recipients' => $admins->pluck('id')->all(),
            ]);

            return $editRequest;
        } catch (\Throwable $e) {
            Log::error("Failed to create edit request notification", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Detect operation type based on model and data.
     */
    protected function detectMethod(Model $model, array $data): string
    {
        if (!$model->exists) {
            return 'create';
        }

        // If no data differences, consider it unchanged
        $dirty = collect($data)->filter(function ($value, $key) use ($model) {
            return $model->{$key} != $value;
        });

        return $dirty->isNotEmpty() ? 'update' : 'none';
    }
}