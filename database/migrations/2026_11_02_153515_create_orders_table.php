<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('table_number', 10)->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('tracking_code', 12)->unique()->nullable();
            $table->enum('payment_status', ['pending', 'paid'])->default(PaymentStatus::Pending->value);
            $table->enum('order_status', ['pending', 'processing', 'ready', 'completed', 'cancelled'])->default(OrderStatus::Pending->value);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('payment_status');
            $table->index('order_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
