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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('teacher_id')->nullable();
        $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        $table->string('description');
        $table->string('image_url');
        $table->timestamps();
    });
        }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
