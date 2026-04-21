<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('route')->unique();
                $table->string('module_key')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_page_permissions')) {
            Schema::create('role_page_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
                $table->boolean('can_view')->default(true);
                $table->timestamps();
                $table->unique(['role_id', 'page_id']);
            });
        }

        if (! Schema::hasTable('feature_toggles')) {
            Schema::create('feature_toggles', function (Blueprint $table) {
                $table->string('feature_key')->primary();
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('module_settings')) {
            Schema::create('module_settings', function (Blueprint $table) {
                $table->string('module_key')->primary();
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->string('setting_key')->primary();
                $table->text('setting_value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('module_settings');
        Schema::dropIfExists('feature_toggles');
        Schema::dropIfExists('role_page_permissions');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('roles');
    }
};
