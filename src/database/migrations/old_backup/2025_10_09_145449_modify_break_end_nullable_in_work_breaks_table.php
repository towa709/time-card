<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('work_breaks', function (Blueprint $table) {
      $table->dateTime('break_end')->nullable()->change();
    });
  }

  public function down(): void
  {
    Schema::table('work_breaks', function (Blueprint $table) {
      $table->dateTime('break_end')->nullable(false)->change();
    });
  }
};
