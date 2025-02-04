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
            $table->foreignId('vendor_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->integer('price');
            $table->integer('final_price')->nullable();
            $table->string('image')->nullable();
            $table->integer("volume")->nullable();
            $table->string("product_code")->unique();
            $table->date("manufacturing_date")->nullable();
            $table->date("expire_date")->nullable();
            $table->string("fragrance_family")->nullable();
            $table->string("gender")->nullable();
            $table->integer('inventory')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_compound_product')->default(false);
            $table->integer('discount')->default(0); // Discount percentage or value
            $table->string('priority')->nullable(); // Priority for sorting discounts
            $table->boolean('status')->default(true);
            $table->foreignId('category_id')->nullable();
            $table->boolean('highlight')->default(false);
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
