<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('brand');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('image');
        });

        foreach (DB::table('products')->cursor() as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'translations' => json_encode([
                        'name' => ['en' => $product->name],
                        'description' => ['en' => $product->description],
                        'brand' => ['en' => $product->brand],
                    ]),
                ]);
        }

        foreach (DB::table('categories')->cursor() as $category) {
            DB::table('categories')
                ->where('id', $category->id)
                ->update([
                    'translations' => json_encode([
                        'name' => ['en' => $category->name],
                    ]),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('translations');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
