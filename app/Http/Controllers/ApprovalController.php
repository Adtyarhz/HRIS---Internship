<?php

namespace App\Http\Controllers;

use App\Models\ChangeDataRequest;
use App\Models\User; // Pastikan User di-import
use App\Services\ApprovalWorkflowService;
use App\Services\CheckerWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApprovalController extends Controller
{
    /**
     * Menampilkan panel approval yang relevan berdasarkan peran user.
     */
    public function index()
    {
        $user = Auth::user();
        $isApprover = optional($user->employee)->position->title === 'HC & GA Manager';

        $pendingForChecking = collect(); // Default koleksi kosong
        $pendingForApproval = collect(); // Default koleksi kosong

        if ($isApprover) {
            // JIKA USER ADALAH APPROVER:
            // Dia hanya peduli dengan request yang sudah diperiksa ('checked')
            $pendingForApproval = ChangeDataRequest::where('status', 'checked')
                ->with(['requester', 'checker'])
                ->latest()
                ->get();
        } else {
            // JIKA USER ADALAH CHECKER BIASA:
            // Dia hanya peduli dengan request 'pending' yang dibuat oleh orang lain
            $pendingForChecking = ChangeDataRequest::where('status', 'pending')
                ->where('requested_by', '!=', $user->id)
                ->with('requester')
                ->latest()
                ->get();
        }

        return view('approvals.index', compact(
            'pendingForChecking',
            'pendingForApproval',
            'isApprover'
        ));
    }

    /**
     * Menampilkan detail dari sebuah request.
     */
    public function show(ChangeDataRequest $cdr)
    {
        return view('approvals.show', compact('cdr'));
    }

    /**
     * Aksi 'check' oleh seorang Checker.
     */
    public function check(ChangeDataRequest $cdr, Request $request)
    {
        try {
            CheckerWorkflowService::check(
                $cdr,
                Auth::user(),
                $request->input('status_notes')
            );
            return redirect()->route('approvals.index')->with('success', "Request #{$cdr->id} telah diperiksa dan diteruskan ke approver.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Aksi 'approve' oleh seorang Approver.
     */
    public function approve(ChangeDataRequest $cdr, Request $request)
    {
        try {
            CheckerWorkflowService::approve(
                $cdr,
                Auth::user(),
                $request->input('status_notes')
            );
            return redirect()->route('approvals.index')->with('success', "Request #{$cdr->id} telah disetujui dan diterapkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Aksi 'reject' oleh Checker atau Approver.
     */
    public function reject(ChangeDataRequest $cdr, Request $request)
    {
        $request->validate(['status_notes' => 'required|string|max:500']);

        try {
            CheckerWorkflowService::reject(
                $cdr,
                Auth::user(),
                $request->input('status_notes')
            );
            return redirect()->route('approvals.index')->with('info', "Request #{$cdr->id} telah ditolak.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
