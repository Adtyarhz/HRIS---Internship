<?php

namespace App\Http\Controllers;

use App\Models\ChangeDataRequest;
use App\Models\User; // Ensure User is imported
use App\Services\ApprovalWorkflowService;
use App\Services\CheckerWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApprovalController extends Controller
{
    /**
     * Display the relevant approval panel based on the user's role.
     */
    public function index()
    {
        $user = Auth::user();
        $isApprover = optional($user->employee)->position->title === 'HC & GA Manager';

        $pendingForChecking = collect(); // Default empty collection
        $pendingForApproval = collect(); // Default empty collection

        if ($isApprover) {
            // IF THE USER IS AN APPROVER:
            // They only care about requests that have been 'checked'.
            $pendingForApproval = ChangeDataRequest::where('status', 'checked')
                ->with(['requester', 'checker'])
                ->latest()
                ->get();
        } else {
            // IF THE USER IS A REGULAR CHECKER:
            // They only care about 'pending' requests made by others.
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
     * Display the details of a request.
     */
    public function show(ChangeDataRequest $cdr)
    {
        return view('approvals.show', compact('cdr'));
    }

    /**
     * The 'check' action by a Checker.
     */
    public function check(ChangeDataRequest $cdr, Request $request)
    {
        try {
            CheckerWorkflowService::check(
                $cdr,
                Auth::user(),
                $request->input('status_notes')
            );
            return redirect()->route('approvals.index')->with('success', "Request #{$cdr->id} has been checked and forwarded to the approver.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * The 'approve' action by an Approver.
     */
    public function approve(ChangeDataRequest $cdr, Request $request)
    {
        try {
            CheckerWorkflowService::approve(
                $cdr,
                Auth::user(),
                $request->input('status_notes')
            );
            return redirect()->route('approvals.index')->with('success', "Request #{$cdr->id} has been approved and applied.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * The 'reject' action by a Checker or Approver.
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
            return redirect()->route('approvals.index')->with('info', "Request #{$cdr->id} has been rejected.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
