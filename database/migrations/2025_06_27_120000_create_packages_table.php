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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_name', 255)->unique(); // e.g., 'admin/users', 'admin/settings'
            $table->string('display_name', 255); // e.g., 'User Manager', 'Setting Manager'
            $table->string('vendor', 100); // e.g., 'admin'
            $table->string('name', 100); // e.g., 'users', 'settings'
            $table->enum('package_type', ['auto_install', 'common', 'industry'])->default('common'); // auto_install, common or industry specific
            $table->string('industry', 100)->nullable(); // null for common packages, industry name for industry packages
            $table->text('description')->nullable(); // package description
            $table->boolean('is_installed')->default(false); // track installation status
            $table->boolean('is_auto_install')->default(false); // whether the package is auto-install
            $table->timestamp('installed_at')->nullable(); // when the package was installed
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['package_type', 'industry']);
            $table->index('is_installed');
            $table->index('is_auto_install');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
}; 