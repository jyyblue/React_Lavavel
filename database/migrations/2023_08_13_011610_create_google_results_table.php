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
        Schema::create('google_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->float('total_price')->default(0.0);
            $table->text('title')->nullable();
            $table->string('seller')->nullable();
            $table->float('item_price')->default(0);
            $table->text('offer_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_results');
    }
};
