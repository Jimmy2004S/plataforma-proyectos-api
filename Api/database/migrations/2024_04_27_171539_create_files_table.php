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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('path');
            $table->unsignedBigInteger('post_id');
            $table->enum('type' , ['cover_image', 'pdf']);

            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->unique(['type' , 'post_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
