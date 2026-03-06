<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_phases', function (Blueprint $table) {
            $table->id();
            $table->string('phase_name', 20)->unique(); // Odd / Even
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        DB::table('academic_phases')->insert([
            ['phase_name' => 'Odd', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['phase_name' => 'Even', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_phases');
    }
};
