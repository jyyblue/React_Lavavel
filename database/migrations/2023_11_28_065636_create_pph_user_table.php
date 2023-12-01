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
        Schema::create('pph_user', function (Blueprint $table) {
            $table->id();
            $table->string('mem_id')->nullable();
            $table->string('shortName')->nullable();
            $table->string('photo')->nullable();
            $table->string('url')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('hourly_rate_converted')->nullable();
            $table->string('job_title')->nullable();
            $table->string('cert')->nullable();
            $table->string('projects_completed')->nullable();
            $table->string('projects_worked_on')->nullable();
            $table->string('projects_completed_ratio')->nullable();
            $table->string('feedback_rating')->nullable();
            $table->string('reviews')->nullable();
            $table->string('response_time')->nullable();
            $table->string('buyers_worked')->nullable();
            $table->string('endorsements')->nullable();
            $table->string('last_project')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pph_user');
    }
};
