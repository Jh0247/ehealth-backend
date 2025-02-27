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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->timestamp('appointment_datetime');
            $table->string('type', 50);
            $table->string('purpose', 255)->nullable();
            $table->integer('duration')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 50);
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
