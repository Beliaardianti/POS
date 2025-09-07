<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_type',
        'reference_id',
        'data',
        'amount',
        'reason',
        'requested_by',
        'approved_by',
        'status',
        'approval_reason',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Methods
    public function approve($userId, $reason = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approval_reason' => $reason,
            'processed_at' => now(),
        ]);

        // Execute approved action
        $this->executeApprovedAction();

        return $this;
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approval_reason' => $reason,
            'processed_at' => now(),
        ]);

        return $this;
    }

    public function executeApprovedAction()
    {
        switch ($this->type) {
            case 'refund':
                $this->processRefund();
                break;
            case 'void':
                $this->processVoid();
                break;
            case 'discount':
                $this->processDiscount();
                break;
            case 'large_transaction':
                $this->processLargeTransaction();
                break;
        }
    }

    private function processRefund()
    {
        $transaction = Transaction::find($this->reference_id);
        if ($transaction) {
            $transaction->update(['status' => 'refunded']);

            // Restore stock
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                $product->increment('stock', $detail->quantity);
            }
        }
    }

    private function processVoid()
    {
        $transaction = Transaction::find($this->reference_id);
        if ($transaction) {
            $transaction->update(['status' => 'voided']);

            // Restore stock
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                $product->increment('stock', $detail->quantity);
            }
        }
    }

    private function processDiscount()
    {
        $transaction = Transaction::find($this->reference_id);
        if ($transaction) {
            $discountData = $this->data;
            $transaction->update([
                'discount_amount' => $discountData['discount_amount'],
                'total' => $discountData['new_total'],
            ]);
        }
    }

    private function processLargeTransaction()
    {
        $transaction = Transaction::find($this->reference_id);
        if ($transaction) {
            $transaction->update(['status' => 'approved']);
        }
    }

    // Static methods untuk create approval
    public static function requestRefund($transactionId, $reason, $amount = null)
    {
        return self::create([
            'type' => 'refund',
            'reference_type' => 'transaction',
            'reference_id' => $transactionId,
            'amount' => $amount,
            'reason' => $reason,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'data' => []
        ]);
    }

    public static function requestVoid($transactionId, $reason)
    {
        return self::create([
            'type' => 'void',
            'reference_type' => 'transaction',
            'reference_id' => $transactionId,
            'reason' => $reason,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'data' => []
        ]);
    }

    public static function requestLargeDiscount($transactionId, $discountData, $reason)
    {
        return self::create([
            'type' => 'discount',
            'reference_type' => 'transaction',
            'reference_id' => $transactionId,
            'amount' => $discountData['discount_amount'],
            'reason' => $reason,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'data' => $discountData
        ]);
    }
}
