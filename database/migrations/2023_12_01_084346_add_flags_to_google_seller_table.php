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
        Schema::table('google_seller', function (Blueprint $table) {
            //
            $table->string('email_flg')->default('0');
            $table->string('agent_flg')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_seller', function (Blueprint $table) {
            //
            $table->dropColumn('email_flg');
            $table->dropColumn('agent_flg');
        });
    }
};
