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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('package_id');
            $table->string('package_name');
            $table->string('package_type');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0.0);
            $table->decimal('remaining_amount', 15, 2);
            $table->integer('progress_percent')->default(0);
            $table->string('next_payment_deadline')->default('25 June 2026');
            $table->string('status')->default('Menabung');
            $table->string('penyaluran_method')->nullable();
            $table->string('penyaluran_receiver')->nullable();
            $table->string('penyaluran_phone')->nullable();
            $table->text('penyaluran_address')->nullable();
            $table->string('penyaluran_status')->nullable();
            $table->string('cert_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
