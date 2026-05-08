<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('type'); // token | subscription
            $table->string('package_key');
            $table->string('package_label');
            $table->unsignedInteger('token_amount')->nullable();
            $table->unsignedInteger('subscription_months')->nullable();
            $table->unsignedInteger('price'); // base price IDR
            $table->unsignedSmallInteger('unique_code')->default(0); // 0-999
            $table->unsignedInteger('total_amount'); // price + unique_code
            $table->string('proof_path')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('status')->default('pending'); // pending, awaiting_review, approved, rejected
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
