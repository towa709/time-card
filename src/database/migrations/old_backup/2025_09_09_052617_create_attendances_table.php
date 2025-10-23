<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('attendances', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->date('work_date');
      $table->dateTime('clock_in')->nullable();
      $table->dateTime('clock_out')->nullable();
      $table->integer('break_time')->nullable();
      $table->integer('total_work_time')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendances');
  }
};
