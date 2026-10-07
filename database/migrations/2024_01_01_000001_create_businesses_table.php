<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('invoice_prefix', 20)->default('INV-');
            $table->unsignedBigInteger('next_invoice_number')->default(1);
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->text('invoice_notes')->nullable();
            $table->text('invoice_terms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
