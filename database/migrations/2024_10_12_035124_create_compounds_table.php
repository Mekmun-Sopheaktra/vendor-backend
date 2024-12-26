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
        Schema::create('compounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id');
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->float('price')->nullable();
            $table->string('image')->nullable();
            $table->string('volume')->nullable();
            $table->string('product_code')->unique();
            $table->date('manufacturing_date')->nullable();
            $table->string('fragrance_family')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('gender')->nullable();
            $table->integer('discount')->default(0);
            $table->string('priority')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compounds');
    }
};
