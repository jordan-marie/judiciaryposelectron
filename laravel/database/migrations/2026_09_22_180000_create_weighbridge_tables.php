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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('field_name');
            $table->enum('field_type', ['text', 'select', 'number'])->default('text');
            $table->string('label');
            $table->boolean('is_required')->default(false);
            $table->json('options_json')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->decimal('initial_weight', 10, 2)->nullable();
            $table->decimal('final_weight', 10, 2)->nullable();
            $table->decimal('net_weight', 10, 2)->nullable();
            $table->string('license_plate')->nullable();
            $table->enum('status', ['PENDING', 'COMPLETED'])->default('PENDING');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('license_plate');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('transaction_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_field_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'form_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_field_values');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
