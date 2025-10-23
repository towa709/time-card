<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('attendance_corrections', function (Blueprint $table) {
      $table->id();
      $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->dateTime('requested_clock_in')->nullable();
      $table->dateTime('requested_clock_out')->nullable();
      $table->integer('requested_break_time')->nullable();
      $table->integer('requested_total_time')->nullable();
      $table->text('note')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendance_corrections');
  }
};
