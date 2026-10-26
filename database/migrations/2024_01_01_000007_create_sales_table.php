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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('sale_number', 50)->unique();
            $table->enum('type', ['rental', 'sale'])->default('rental');
            $table->date('rental_start_date')->nullable();
            $table->date('rental_end_date')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->string('payment_method', 50)->default('cash');
            $table->enum('payment_status', ['paid', 'pending'])->default('paid');
            $table->text('notes')->nullable();
            $table->dateTime('sold_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
