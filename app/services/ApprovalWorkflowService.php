<?php

namespace App\Services;

use App\Models\ChangeDataRequest;
use App\Models\User;
use App\Notifications\ApprovalRequestCreatedNotification; // <-- Ditambahkan
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
    /**
     * Menerjemahkan metode HTTP ke tipe aksi.
     */
    public static function captureModelChange($user, $model, string $action): ?ChangeDataRequest
    {
        $modelClass = get_class($model);
        $modelId = $action !== 'create' ? $model->getKey() : null;

        $changes = match ($action) {
            'update' => ['old' => $model->getOriginal(), 'new' => $model->getAttributes()],
            default => ['data' => $model->getAttributes()],
        };

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

            Log::info("ChangeDataRequest #{$cdr->id} dibuat untuk {$modelClass} oleh User #{$userId}");
            // V-- NOTIFIKASI DIAKTIFKAN --V
            self::notifyCheckers($cdr);
            return $cdr;

        } catch (\Throwable $e) {
            Log::error("Gagal membuat ChangeDataRequest: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengirim notifikasi ke semua kandidat Checker (staf HC selain pembuat).
     */
    public static function notifyCheckers(ChangeDataRequest $cdr): void
    {
        $channels = config('approval.notification_channels', ['database', 'mail']); // <-- Menambahkan mail sebagai default
        $checkers = User::where('role', 'hc')
            ->where('id', '<>', $cdr->requested_by)
            ->get();

        foreach ($checkers as $user) {
            // V-- NOTIFIKASI DIAKTIFKAN --V
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
                    $model = $modelClass::create($changes['data']);
                    $cdr->model_id = $model->getKey();
                    Log::info("Request #{$cdr->id} applied: CREATED {$modelClass} #{$cdr->model_id}");
                    break;

                case 'update':
                    $model = $modelClass::find($cdr->model_id);
                    if (!$model) throw new Exception("Model {$modelClass} #{$cdr->model_id} tidak ditemukan.");
                    $model->update($changes['new']);
                    Log::info("Request #{$cdr->id} applied: UPDATED {$modelClass} #{$cdr->model_id}");
                    break;

                case 'delete':
                    $model = $modelClass::find($cdr->model_id);
                    $model?->delete(); // Hapus jika modelnya ada
                    Log::info("Request #{$cdr->id} applied: DELETED {$modelClass} #{$cdr->model_id}");
                    break;
            }

            $cdr->status = 'applied';
            $cdr->applied_at = now();
            $cdr->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menerapkan Request #{$cdr->id}: " . $e->getMessage());

            $cdr->status = 'failed';
            $cdr->failed_at = now();
            $cdr->status_notes = 'Gagal diterapkan: ' . $e->getMessage();
            $cdr->save();

            // Lemparkan kembali agar controller tahu ada masalah
            throw $e;
        }
    }
}

