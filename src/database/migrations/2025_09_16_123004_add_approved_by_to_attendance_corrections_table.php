<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('attendance_corrections', function (Blueprint $table) {
      // 承認した管理者のIDを記録するカラム
      $table->unsignedBigInteger('approved_by')->nullable()->after('status');

      // 外部キー制約（adminsテーブルのid）
      $table->foreign('approved_by')->references('id')->on('admins')->onDelete('set null');
    });
  }

  public function down(): void
  {
    Schema::table('attendance_corrections', function (Blueprint $table) {
      $table->dropForeign(['approved_by']);
      $table->dropColumn('approved_by');
    });
  }
};
