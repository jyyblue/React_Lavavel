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
        Schema::create('google_scrape_history', function (Blueprint $table) {
            $table->id();
            $table->integer('call_group_id')->default(0);
            $table->integer('product_id')->default(0);
            $table->string('call_time')->nullable();
            $table->integer('sz_success')->default(0);
            $table->integer('sz_fail')->default(0);
            $table->integer('sz_zero')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_scrape_history');
    }
};
