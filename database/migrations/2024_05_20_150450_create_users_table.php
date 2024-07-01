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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->string('name', 100);
            $table->string('profile_img')->nullable();
            $table->string('email', 100)->unique();
            $table->string('contact', 15)->nullable();
            $table->string('icno', 14)->unique()->nullable();
            $table->string('password', 255);
            $table->string('user_role', 50);
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
        Schema::dropIfExists('users');
    }
};
