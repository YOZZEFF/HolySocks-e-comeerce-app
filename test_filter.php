<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "=== CATEGORY: men ===\n";
$products = Product::whereHas('category', function($q) {
    $q->where('slug', 'men')->orWhereHas('parent', fn($q2) => $q2->where('slug', 'men'));
})->with('category')->get();

echo "Count: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "  {$p->id}: {$p->name} [cat: {$p->category->name}, slug: {$p->category->slug}]\n";
}

echo "\n=== CATEGORY: women ===\n";
$products2 = Product::whereHas('category', function($q) {
    $q->where('slug', 'women')->orWhereHas('parent', fn($q2) => $q2->where('slug', 'women'));
})->with('category')->get();

echo "Count: " . $products2->count() . "\n";
foreach ($products2 as $p) {
    echo "  {$p->id}: {$p->name} [cat: {$p->category->name}, slug: {$p->category->slug}]\n";
}
