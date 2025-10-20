<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('attendance_correction_breaks', function (Blueprint $table) {
      $table->dateTime('break_start')->change();
      $table->dateTime('break_end')->change();
    });
  }

  public function down(): void
  {
    Schema::table('attendance_correction_breaks', function (Blueprint $table) {
      $table->time('break_start')->change();
      $table->time('break_end')->change();
    });
  }
};
