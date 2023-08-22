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
        Schema::table('amazon_seller', function (Blueprint $table) {
            //
            $table->string('sales_agent_name')->nullable();
            $table->string('sales_agent_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amazon_seller', function (Blueprint $table) {
            //
            $table->dropColumn('sales_agent_name');
            $table->dropColumn('sales_agent_email');
        });
    }
};
