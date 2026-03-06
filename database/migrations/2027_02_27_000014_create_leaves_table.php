<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('leaves');
        
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->morphs('leaveable'); // Student or Teacher
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->virtualAs('DATEDIFF(end_date, start_date) + 1');
            $table->enum('leave_type', ['sick', 'casual', 'emergency', 'other'])->default('casual');
            $table->text('reason');
            $table->string('attachment')->nullable(); // Medical certificate, etc.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('approval_remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
