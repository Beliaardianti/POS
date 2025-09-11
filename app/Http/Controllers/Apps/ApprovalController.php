<?php

namespace App\Http\Controllers\Apps;

use App\Models\Approval;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display pending approvals for owner
     */
    public function index()
    {
        $this->authorize('approvals.view');

        $approvals = Approval::with(['requestedBy'])
            ->pending()
            ->latest('requested_at')
            ->paginate(20);

        return inertia('Apps/Approvals/Index', [
            'approvals' => $approvals,
            'stats' => $this->getApprovalStats()
        ]);
    }

    /**
     * Show approval details
     */
    public function show(Approval $approval)
    {
        $this->authorize('approvals.view');

        $approval->load(['requestedBy', 'approvedBy']);

        return inertia('Approvals/Show', [
            'approval' => $approval
        ]);
    }

    /**
     * Approve the approval request - DEBUG VERSION
     */
    /**
     * Approve the approval request - FINAL FIX
     */
    public function approve(Request $request, Approval $approval)
    {
        $this->authorize('approvals.approve');

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            // STEP 1: Manual update approval (bypass model method)
            $approval->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->reason
            ]);

            // STEP 2: Manual update transaction status
            $transaction = Transaction::find($approval->reference_id);

            if ($transaction && $approval->reference_type === 'transaction') {
                if ($approval->type === 'void') {
                    $transaction->update(['status' => 'voided']);

                    // Kembalikan stok
                    foreach ($transaction->details as $detail) {
                        $product = Product::find($detail->product_id);
                        if ($product) {
                            $product->increment('stock', $detail->qty);
                        }
                    }
                } elseif ($approval->type === 'refund') {
                    $transaction->update(['status' => 'refunded']);

                    // Kembalikan stok
                    foreach ($transaction->details as $detail) {
                        $product = Product::find($detail->product_id);
                        if ($product) {
                            $product->increment('stock', $detail->qty);
                        }
                    }
                }

                // Verify update berhasil
                $updatedTransaction = Transaction::find($approval->reference_id);

                \Log::info('Transaction status updated successfully', [
                    'transaction_id' => $transaction->id,
                    'old_status' => $transaction->getOriginal('status'),
                    'new_status' => $updatedTransaction->status,
                    'approval_type' => $approval->type
                ]);
            }

            return back()->with('success', 'Approval request has been approved successfully. Transaction status updated to: ' . ($transaction->fresh()->status ?? 'unknown'));
        } catch (\Exception $e) {
            \Log::error('Approval failed', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject the approval request - UPDATED
     */
    public function reject(Request $request, Approval $approval)
    {
        $this->authorize('approvals.reject');

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Manual update approval (bypass model method)
            $approval->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->reason
            ]);

            // Reset transaction status ke completed
            $transaction = Transaction::find($approval->reference_id);
            if ($transaction && $approval->reference_type === 'transaction') {
                $transaction->update(['status' => 'completed']);

                \Log::info('Transaction status reset after rejection', [
                    'transaction_id' => $transaction->id,
                    'status' => 'completed'
                ]);
            }

            return back()->with('success', 'Approval request has been rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Request refund approval
     */
    public function requestRefund(Request $request)
    {
        $this->authorize('approvals.request');

        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Check if already has pending approval
        $existingApproval = Approval::where('type', 'refund')
            ->where('reference_id', $transaction->id)
            ->pending()
            ->first();

        if ($existingApproval) {
            return back()->with('error', 'Refund approval request already exists for this transaction.');
        }

        Approval::requestRefund(
            $transaction->id,
            $request->reason,
            $request->amount ?? $transaction->grand_total
        );

        return back()->with('success', 'Refund approval request has been sent to owner.');
    }

    /**
     * Request void approval
     */
    public function requestVoid(Request $request)
    {
        $this->authorize('approvals.request');

        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reason' => 'required|string|max:500'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        Approval::requestVoid($transaction->id, $request->reason);

        return back()->with('success', 'Void approval request has been sent to owner.');
    }

    /**
     * Request large discount approval
     */
    public function requestDiscount(Request $request)
    {
        $this->authorize('approvals.request');

        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'discount_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        $discountData = [
            'discount_amount' => $request->discount_amount,
            'original_total' => $transaction->grand_total,
            'new_total' => $transaction->grand_total - $request->discount_amount
        ];

        Approval::requestLargeDiscount(
            $transaction->id,
            $discountData,
            $request->reason
        );

        return back()->with('success', 'Discount approval request has been sent to owner.');
    }

    /**
     * Get approval statistics for dashboard
     */
    private function getApprovalStats()
    {
        return [
            'pending_count' => Approval::pending()->count(),
            'approved_today' => Approval::approved()
                ->whereDate('processed_at', today())
                ->count(),
            'rejected_today' => Approval::rejected()
                ->whereDate('processed_at', today())
                ->count(),
        ];
    }

    /**
     * Get approval history
     */
    public function history(Request $request)
    {
        $this->authorize('approvals.view');

        $query = Approval::with(['requestedBy', 'approvedBy'])
            ->where('status', '!=', 'pending')
            ->latest('processed_at');

        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->start_date) {
            $query->whereDate('processed_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('processed_at', '<=', $request->end_date);
        }

        $approvals = $query->paginate(20);

        return inertia('Apps/Approvals/History', [
            'approvals' => $approvals,
            'filters' => $request->only(['type', 'start_date', 'end_date'])
        ]);
    }

    /**
     * Test create approval - for development only
     */
    public function testCreate()
    {
        $approval = Approval::requestRefund(1, 'Testing approval system', 50000);

        return response()->json([
            'message' => 'Test approval created',
            'approval' => $approval
        ]);
    }
}
