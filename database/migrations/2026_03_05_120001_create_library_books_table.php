<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable()->index();
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('available_copies')->default(0);
            $table->string('shelf_location')->nullable();
            $table->string('status')->default('available');
            $table->string('cover_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
