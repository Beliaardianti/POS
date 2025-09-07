<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'refund', 'void', 'discount', 'large_transaction'
            $table->string('reference_type'); // 'transaction', 'payment', etc
            $table->unsignedBigInteger('reference_id');
            $table->json('data'); // Data yang perlu di-approve
            $table->decimal('amount', 15, 2)->nullable(); // Jumlah yang di-approve
            $table->text('reason'); // Alasan request approval
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_reason')->nullable(); // Alasan approve/reject
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');

            // Indexes
            $table->index(['status', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
