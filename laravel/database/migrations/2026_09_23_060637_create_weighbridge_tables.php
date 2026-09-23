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
        // 1. forms table
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. form_role pivot table (linking forms with spatie/laravel-permission roles)
        Schema::create('form_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. form_fields table
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('field_name');
            $table->string('field_type'); // text, number, select, datetime, checkbox
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // JSON array/object for select options or field metadata
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('in_progress'); // in_progress, completed, cancelled
            $table->decimal('gross_weight', 10, 2)->default(0.00);
            $table->decimal('tare_weight', 10, 2)->default(0.00);
            $table->decimal('net_weight', 10, 2)->default(0.00);
            $table->string('plate_number')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. transaction_meta table (EAV structure for dynamic dynamic fields)
        Schema::create('transaction_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('field_name');
            $table->text('field_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_meta');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_role');
        Schema::dropIfExists('forms');
    }
};
