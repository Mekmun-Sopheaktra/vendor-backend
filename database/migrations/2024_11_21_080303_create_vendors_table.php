<?php

use App\Constants\VendorStatusConstants;
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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('banner')->nullable();
            $table->string('status')->default(VendorStatusConstants::PENDING);
            $table->string('purpose')->nullable();
            $table->string('paypal_client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
