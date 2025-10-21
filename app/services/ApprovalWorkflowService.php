<?php

namespace App\Services;

use App\Models\ChangeDataRequest;
use App\Models\User;
use App\Notifications\ApprovalRequestCreatedNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApprovalWorkflowService
{
    /**
     * Menerjemahkan metode HTTP ke tipe aksi.
     */
    public static function captureModelChange($user, $model, string $action, array $extraData = []): ?ChangeDataRequest
    {
        // pastikan relasi utama dimuat sebelum clone
        if (method_exists($model, 'load')) {
            try {
                $model->loadMissing(['certificationMaterials']);
            } catch (\Throwable $e) {
                Log::warning("Relasi certificationMaterials tidak ditemukan pada " . get_class($model));
            }
        }

        $modelClass = get_class($model);
        $modelId = $action !== 'create' ? $model->getKey() : null;

        $changes = match ($action) {
            'update' => [
                'old' => $model->getOriginal(),
                'new' => $model->getAttributes(),
            ],
            default => ['data' => $model->getAttributes()],
        };

        // 🔹 Tambahkan snapshot relasi (misal certificationMaterials)
        $relations = [];
        if (method_exists($model, 'certificationMaterials')) {
            try {
                $relations['certification_materials'] = $model->certificationMaterials()
                    ->get(['id', 'file_path', 'description'])
                    ->toArray();
            } catch (\Throwable $e) {
                Log::warning("Gagal mengambil relasi certificationMaterials: " . $e->getMessage());
            }
        }

        if (!empty($relations)) {
            $changes['relations'] = $relations;
        }

        // 🔹 Tambahkan extra data (misal new_materials & delete_materials)
        if (!empty($extraData)) {
            $changes['extra'] = $extraData;
        }

        return self::createRequest($user->id, $modelClass, $modelId, $action, $changes);
    }

    /**
     * Pusat pembuatan ChangeDataRequest.
     */
    protected static function createRequest($userId, $modelClass, $modelId, $action, array $changes): ?ChangeDataRequest
    {
        try {
            $cdr = DB::transaction(function () use ($userId, $modelClass, $modelId, $action, $changes) {
                $ttlDays = config('approval.ttl_days', 7);
                return ChangeDataRequest::create([
                    'model' => $modelClass,
                    'model_id' => $modelId,
                    'action' => $action,
                    'changes' => $changes,
                    'status' => 'pending',
                    'requested_by' => $userId,
                    'expired_at' => Carbon::now()->addDays($ttlDays),
                ]);
            });

            Log::info("✅ ChangeDataRequest #{$cdr->id} dibuat untuk {$modelClass} oleh User #{$userId}");
            self::notifyCheckers($cdr);
            return $cdr;

        } catch (\Throwable $e) {
            Log::error("❌ Gagal membuat ChangeDataRequest: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengirim notifikasi ke semua kandidat Checker (staf HC selain pembuat).
     */
    public static function notifyCheckers(ChangeDataRequest $cdr): void
    {
        $channels = config('approval.notification_channels', ['database', 'mail']);
        $checkers = User::where('role', 'hc')
            ->where('id', '<>', $cdr->requested_by)
            ->get();

        foreach ($checkers as $user) {
            $user->notify((new ApprovalRequestCreatedNotification($cdr))->setChannels($channels));
        }
    }

    /**
     * Menerapkan perubahan dari CDR ke model target.
     */
    public static function apply(ChangeDataRequest $cdr): void
    {
        if ($cdr->status !== 'approved') {
            throw new Exception("Hanya request berstatus 'approved' yang dapat diterapkan.");
        }

        DB::beginTransaction();
        try {
            $modelClass = $cdr->model;
            $changes = $cdr->changes;

            switch ($cdr->action) {
                case 'create':
                    // 🔹 Buat model baru
                    $model = $modelClass::create($changes['data']);
                    $cdr->model_id = $model->getKey();

                    // 🔹 Jika ada file tambahan (materials), pindahkan dari temp ke folder final
                    if (!empty($changes['extra']['related_files'])) {
                        self::applyRelatedFiles($model, $changes['extra']['related_files']);
                    }

                    // 🔹 Jika ada sertifikat (certificate_file) di temp, pindahkan juga
                    self::handleCertificateFile($model);

                    Log::info("Request #{$cdr->id} applied: CREATED {$modelClass} #{$cdr->model_id}");
                    break;

                case 'update':
                    $model = $modelClass::find($cdr->model_id);
                    if (!$model)
                        throw new Exception("Model {$modelClass} #{$cdr->model_id} tidak ditemukan.");

                    // Update field utama
                    $model->update($changes['new']);

                    // 🔹 Tangani relasi file jika ada
                    if (!empty($changes['extra']['related_files'])) {
                        self::applyRelatedFiles($model, $changes['extra']['related_files']);
                    }

                    // 🔹 Tangani certificate_file jika path mengandung 'temp/'
                    self::handleCertificateFile($model);

                    Log::info("Request #{$cdr->id} applied: UPDATED {$modelClass} #{$cdr->model_id}");
                    break;

                case 'delete':
                    $model = $modelClass::find($cdr->model_id);

                    if ($model) {
                        // 🔹 Hapus semua materials terkait dari storage
                        if (method_exists($model, 'certificationMaterials')) {
                            foreach ($model->certificationMaterials as $material) {
                                $filePath = 'certifications/materials/' . $material->file_path;
                                if (Storage::disk('public')->exists($filePath)) {
                                    Storage::disk('public')->delete($filePath);
                                }
                            }
                            // Hapus record relasinya di DB
                            $model->certificationMaterials()->delete();
                        }

                        // 🔹 Hapus juga file certificate jika ada
                        if (!empty($model->certificate_file)) {
                            if (Storage::disk('public')->exists($model->certificate_file)) {
                                Storage::disk('public')->delete($model->certificate_file);
                            }
                        }

                        $model->delete();
                        Log::info("Request #{$cdr->id} applied: DELETED {$modelClass} #{$cdr->model_id}");
                    } else {
                        Log::warning("Request #{$cdr->id}: Model {$modelClass} #{$cdr->model_id} tidak ditemukan untuk dihapus.");
                    }
                    break;
            }

            $cdr->update([
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menerapkan Request #{$cdr->id}: " . $e->getMessage());

            $cdr->update([
                'status' => 'failed',
                'failed_at' => now(),
                'status_notes' => 'Gagal diterapkan: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Menangani pemindahan file certificate jika di temp.
     */
    protected static function handleCertificateFile($model): void
    {
        if (empty($model->certificate_file) || !str_contains($model->certificate_file, '/temp/')) {
            return;
        }

        $tempPath = $model->certificate_file; // Misal: 'certifications/temp/123_cert.pdf'
        $finalPath = str_replace('/temp/', '/', $tempPath); // Menjadi: 'certifications/123_cert.pdf'
        $fileName = basename($finalPath); // Hanya nama file: '123_cert.pdf'

        // Pindahkan file fisik menggunakan Storage facade untuk keamanan
        if (Storage::disk('public')->exists($tempPath)) {
            if (!Storage::disk('public')->move($tempPath, $finalPath)) {
                throw new Exception("Gagal memindahkan certificate_file dari {$tempPath} ke {$finalPath}");
            }
        } else {
            throw new Exception("File certificate temp tidak ditemukan: {$tempPath}");
        }

        // Update path di model/DB hanya nama file
        $model->certificate_file = 'certifications/' . $fileName;
        $model->save();
    }

    protected static function applyRelatedFiles($model, array $relatedFiles): void
    {
        // 🔹 1. Pindahkan file baru dari temp ke folder final
        if (!empty($relatedFiles['new_materials'])) {
            foreach ($relatedFiles['new_materials'] as $tempFile) {
                // Asumsi $tempFile adalah string path relatif seperti 'certifications/materials/temp/xxxx.jpg'
                $filename = basename(is_array($tempFile) ? ($tempFile['file_path'] ?? $tempFile) : $tempFile);
                $finalFile = str_replace('/temp/', '/', $tempFile); // Menjadi 'certifications/materials/xxxx.jpg'

                // Pindahkan file fisik
                if (Storage::disk('public')->exists($tempFile)) {
                    if (!Storage::disk('public')->move($tempFile, $finalFile)) {
                        throw new Exception("Gagal memindahkan material file dari {$tempFile} ke {$finalFile}");
                    }
                } else {
                    throw new Exception("File material temp tidak ditemukan: {$tempFile}");
                }

                // Simpan ke tabel certification_materials
                if (method_exists($model, 'certificationMaterials')) {
                    $model->certificationMaterials()->create([
                        // disimpan RELATIF agar cocok dengan Blade index
                        'file_path' => self::normalizeFilePath($filename),
                        'description' => null,
                    ]);
                }
            }
        }

        // 🔹 2. Hapus file yang dihapus (delete_materials)
        if (!empty($relatedFiles['delete_materials'])) {
            foreach ($relatedFiles['delete_materials'] as $oldPath) {
                // Asumsi $oldPath adalah string path relatif seperti 'certifications/materials/xxxx.jpg'
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                if (method_exists($model, 'certificationMaterials')) {
                    $model->certificationMaterials()
                        ->where('file_path', self::normalizeFilePath($oldPath))
                        ->delete();
                }
            }
        }
    }

    protected static function normalizeFilePath($path): string
    {
        return str_replace(['\\', '//'], '/', trim($path, '/'));
    }
}