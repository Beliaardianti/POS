<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'cashier_id',
        'customer_id',
        'invoice',
        'cash',
        'change',
        'discount',
        'grand_total',
        'status'  // TAMBAH INI - SATU BARIS AJA!
    ];

    /**
     * details
     *
     * @return void
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * customer
     *
     * @return void
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * cashier
     *
     * @return void
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * profits
     *
     * @return void
     */
    public function profits()
    {
        return $this->hasMany(Profit::class);
    }

    /**
     * createdAt
     *
     * @return Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('d-M-Y H:i:s'),
        );
    }

    // approval

    // TAMBAH RELATIONSHIP APPROVAL - INI YANG KURANG
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'reference');
    }

    public function pendingApproval()
    {
        return $this->morphOne(Approval::class, 'reference')
            ->where('status', 'pending');
    }

    // Status constants (optional)
    const STATUS_COMPLETED = 'completed';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_VOIDED = 'voided';
}
