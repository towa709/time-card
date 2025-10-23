<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('attendance_corrections', function (Blueprint $table) {
      $table->time('requested_clock_in')->nullable()->change();
      $table->time('requested_clock_out')->nullable()->change();
    });
  }

  public function down(): void
  {
    Schema::table('attendance_corrections', function (Blueprint $table) {
      $table->dateTime('requested_clock_in')->nullable()->change();
      $table->dateTime('requested_clock_out')->nullable()->change();
    });
  }
};
