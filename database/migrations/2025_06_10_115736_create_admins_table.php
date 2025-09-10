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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',100)->nullable();
            $table->string('last_name',100)->nullable();
            $table->string('email',100)->nullable();
            $table->string('mobile',100)->nullable();
            $table->string('website_name')->nullable();
            $table->string('website_slug')->nullable();
            $table->string('password')->nullable();
            $table->string('industry',100)->nullable();
            $table->boolean('is_dummy_data')->default(false); 
            $table->boolean('status')->nullable()->default(true);
            $table->string('remember_token')->nullable();   
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};