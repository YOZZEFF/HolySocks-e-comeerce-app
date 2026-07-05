<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'size', 'color']);
            $table->dropColumn('size');
            $table->unique(['product_id', 'color']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'color']);
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->nullable();
            $table->unique(['product_id', 'size', 'color']);
        });
    }
};
