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
    Schema::table('attendances', function (Blueprint $table) {
      $table->string('note', 255)->nullable()->after('total_work_time');
    });
  }

  public function down(): void
  {
    Schema::table('attendances', function (Blueprint $table) {
      $table->dropColumn('note');
    });
  }
};
