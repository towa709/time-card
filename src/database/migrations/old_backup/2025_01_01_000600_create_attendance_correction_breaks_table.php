<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('attendance_correction_breaks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('attendance_correction_id')->constrained('attendance_corrections')->onDelete('cascade');
      $table->dateTime('break_start');
      $table->dateTime('break_end')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendance_correction_breaks');
  }
};
