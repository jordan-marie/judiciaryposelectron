<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->string('status')->default('in_progress'); // in_progress, completed, cancelled
            $table->decimal('gross_weight', 10, 2)->default(0);
            $table->decimal('tare_weight', 10, 2)->default(0);
            $table->decimal('net_weight', 10, 2)->default(0);
            $table->string('plate_number')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
