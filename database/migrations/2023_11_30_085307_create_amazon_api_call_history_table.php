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
        Schema::create('amazon_api_call_history', function (Blueprint $table) {
            $table->id();
            $table->integer('call_group_id')->default(0);
            $table->integer('call_sub_group_id')->default(0);
            $table->string('call_time')->nullable();
            $table->integer('sz_empty_asin')->default(0);
            $table->integer('sz_success')->default(0);
            $table->integer('sz_fail')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amazon_api_call_history');
    }
};
