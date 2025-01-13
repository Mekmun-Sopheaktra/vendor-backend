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
        Schema::create('baskets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id');
            $table->foreignId('user_id');
            $table->foreignId('product_id');

            $table->integer('count')->default(1);
            //price of the product at the time of adding to the basket
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('status', ['created', 'pending_payment', 'paid', 'cancelled'])->default('created');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baskets');
    }
};
