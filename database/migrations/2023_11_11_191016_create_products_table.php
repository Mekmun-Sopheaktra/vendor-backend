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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string("brand_id");
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->integer('price');
            $table->string('image')->nullable();
            $table->integer("volume")->default('0');
            $table->string("product_code")->nullable();
            $table->date("manufacturing_date")->nullable();
            $table->date("expire_date")->nullable();
            $table->string("fragrance_family")->nullable();
            $table->string("gender");
            $table->integer('inventory')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_compound_product')->default(false);
            $table->integer('discount')->default(0); // Discount percentage or value
            $table->string('priority')->nullable(); // Priority for sorting discounts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
