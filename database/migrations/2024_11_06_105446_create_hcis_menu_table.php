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
        Schema::create('hcis_menu', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);
            $table->string('title', 100);
            $table->string('route', 100);
            $table->string('image', 100);
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcis_menu');
    }
};
