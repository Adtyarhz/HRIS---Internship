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
     * Buat request perubahan data (create/update/delete) dan kirim notifikasi ke HC/Superadmin.
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

            // Cari class EditRequest-nya (misal: EmployeeEditRequest)
            $editRequestClass = "App\\Models\\{$modelName}EditRequest";
            if (!class_exists($editRequestClass)) {
                // fallback: gunakan EmployeeEditRequest default
                $editRequestClass = "App\\Models\\EmployeeEditRequest";
            }

            // Tentukan method otomatis jika belum diberikan
            $method = $operation ?? $this->detectMethod($model, $validatedData);

            // Data original hanya untuk update/delete
            $originalData = null;
            if ($method === 'update' && $model->exists) {
                $originalData = $model->only(array_keys($validatedData));
            }

            // Buat request perubahan data
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

            // Kirim notifikasi ke HC & Superadmin
            $admins = User::whereIn('role', ['hc', 'superadmin'])
                ->whereKeyNot($user->id)
                ->get();

            if ($admins->isEmpty()) {
                Log::warning("Tidak ada penerima notifikasi HC/Superadmin untuk {$modelName}.");
                return $editRequest;
            }

            foreach ($admins as $admin) {
                $admin->notify(new $notificationClass(
                    $user->name ?? 'Karyawan',
                    $editRequest->id
                ));
            }

            Log::info("Notifikasi {$method} request berhasil dikirim.", [
                'model' => $modelName,
                'request_id' => $editRequest->id,
                'recipients' => $admins->pluck('id')->all(),
            ]);

            return $editRequest;
        } catch (\Throwable $e) {
            Log::error("Gagal membuat request notifikasi edit", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Deteksi jenis operasi berdasarkan model & datanya.
     */
    protected function detectMethod(Model $model, array $data): string
    {
        if (!$model->exists) {
            return 'create';
        }

        // Jika tidak ada perbedaan data, anggap tidak berubah
        $dirty = collect($data)->filter(function ($value, $key) use ($model) {
            return $model->{$key} != $value;
        });

        return $dirty->isNotEmpty() ? 'update' : 'none';
    }
}
