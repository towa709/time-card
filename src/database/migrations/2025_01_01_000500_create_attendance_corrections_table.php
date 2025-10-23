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
      $table->time('requested_clock_in')->nullable();
      $table->time('requested_clock_out')->nullable();
      $table->integer('requested_break_time')->nullable();
      $table->integer('requested_total_time')->nullable();
      $table->text('note')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->foreign('approved_by')->references('id')->on('admins')->onDelete('set null');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendance_corrections');
  }
};
