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
        Schema::create('admin_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('otp_code', 6);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->string('type')->default('login'); // login, password_reset, etc.
            $table->timestamps();

            $table->index(['admin_id', 'otp_code', 'is_used']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_otps');
    }
};
