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
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('title')->nullable();
            $table->string('category')->nullable();
            $table->string('stock')->nullable();
            $table->float('price')->default(0.0);
            $table->string('asin')->nullable();
            $table->string('listino')->nullable();
            $table->string('ean')->nullable();
            $table->string('google_id')->nullable();
            $table->text('google_url')->nullable();
            $table->integer('cron_flg')->default(0);
            $table->integer('cron_flg_amazon')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
