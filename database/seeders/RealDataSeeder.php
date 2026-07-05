<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RealDataSeeder extends Seeder
{
    private const ALLOWED_COLORS = ['black', 'white', 'brown', 'blue', 'gray'];

    private int $productCount = 0;

    private const MAX_PRODUCTS = PHP_INT_MAX;

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Review::truncate();
        OrderItem::truncate();
        Order::truncate();
        Payment::truncate();
        Address::truncate();
        CartItem::truncate();
        Wishlist::truncate();
        ProductImage::truncate();
        ProductVariant::truncate();
        Product::truncate();
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $menSocks = Category::create(['name' => 'Man Socks', 'slug' => 'man-socks']);
        $womenSocks = Category::create(['name' => 'Woman Socks', 'slug' => 'woman-socks']);
        $kidsSocks = Category::create(['name' => 'Kids Socks', 'slug' => 'kids-socks']);

//  this for subCategories
        // $cat = fn($name , $slug , $parent) => Category::create(
        //     [
        //         'name' => $name ,
        //         'slug' => $slug ,
        //         'parent_id' => $parent->id
        //     ]
        // );

        // $kidsSocks = $cat('kids neek' , 'kids-neek' , $kidsSocks);

        $slug = fn($name) => Str::slug($name);

        $dataset = [
            // ─────────────────────────────────────────────
            // MEN: Socks
            // ─────────────────────────────────────────────
            [[
                'name' => 'Classic Crew Socks', 'brand' => 'Comfort Step',
                'description' => 'Everyday crew-length socks in premium combed cotton. Reinforced heel and toe with a cushioned sole for all-day comfort. Ribbed cuff stays up without binding.',
                'is_featured' => false, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'black', 'price' => 12, 'stock' => 60],
                    ['color' => 'white', 'price' => 12, 'stock' => 60],
                    ['color' => 'gray', 'price' => 12, 'stock' => 50],
                    ['color' => 'blue', 'price' => 12, 'stock' => 40],
                ],
            ], $menSocks],
            [[
                'name' => 'No-Show Loafers Socks', 'brand' => 'Invisible Edge',
                'description' => 'Ultra-low cut no-show socks designed for loafers and low-top sneakers. Silicone heel grip prevents slipping. Moisture-wicking bamboo viscose blend.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'black', 'price' => 10, 'stock' => 80],
                    ['color' => 'white', 'price' => 10, 'stock' => 70],
                    ['color' => 'brown', 'price' => 10, 'stock' => 45],
                ],
            ], $menSocks],
            [[
                'name' => 'Merino Wool Boot Socks', 'brand' => 'Alpine Layer',
                'description' => 'Mid-calf boot socks in temperature-regulating merino wool. Cushioned throughout with a smooth toe seam. Naturally odor-resistant for multi-day wear.',
                'is_featured' => true, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'gray', 'price' => 18, 'stock' => 40],
                    ['color' => 'black', 'price' => 18, 'stock' => 35],
                    ['color' => 'brown', 'price' => 18, 'stock' => 30],
                ],
            ], $menSocks],
            [[
                'name' => 'Athletic Quarter Socks', 'brand' => 'Active Sole',
                'description' => 'Performance quarter-length socks with cushioned sole and arch support. Moisture-wicking fabric keeps feet dry during workouts. Reinforced heel and toe for durability.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'white', 'price' => 11, 'stock' => 75],
                    ['color' => 'black', 'price' => 11, 'stock' => 65],
                    ['color' => 'gray', 'price' => 11, 'stock' => 50],
                    ['color' => 'blue', 'price' => 11, 'stock' => 40],
                ],
            ], $menSocks],
            // ─────────────────────────────────────────────
            // WOMEN: Socks
            // ─────────────────────────────────────────────
            [[
                'name' => 'Cozy Knit Socks', 'brand' => 'Snug Studio',
                'description' => 'Plush mid-calf knit socks in a soft cotton-acrylic blend. Ribbed fold-over cuff with a brushed interior for extra warmth. Perfect for lounging or layering with boots.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'black', 'price' => 14, 'stock' => 55],
                    ['color' => 'white', 'price' => 14, 'stock' => 50],
                    ['color' => 'gray', 'price' => 14, 'stock' => 45],
                    ['color' => 'brown', 'price' => 14, 'stock' => 35],
                ],
            ], $womenSocks],
            [[
                'name' => 'Ankle Trainer Socks', 'brand' => 'Active Sole',
                'description' => 'Breathable ankle-high socks for training and everyday wear. Mesh ventilation panels with arch support and a reinforced heel. Moisture-wicking performance knit.',
                'is_featured' => false, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'white', 'price' => 10, 'stock' => 70],
                    ['color' => 'black', 'price' => 10, 'stock' => 65],
                    ['color' => 'blue', 'price' => 10, 'stock' => 40],
                    ['color' => 'gray', 'price' => 10, 'stock' => 45],
                ],
            ], $womenSocks],
            [[
                'name' => 'Knee High Socks', 'brand' => 'Snug Studio',
                'description' => 'Classic knee-high socks in a soft cotton blend with stretch ribbing. Versatile length pairs well with boots, skirts, or layered over tights. Reinforced toe and heel.',
                'is_featured' => true, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'black', 'price' => 16, 'stock' => 50],
                    ['color' => 'white', 'price' => 16, 'stock' => 45],
                    ['color' => 'gray', 'price' => 16, 'stock' => 40],
                    ['color' => 'brown', 'price' => 16, 'stock' => 30],
                ],
            ], $womenSocks],
            [[
                'name' => 'Lace Trim Socks', 'brand' => 'Whimsy & Co.',
                'description' => 'Delicate ankle socks with an elegant lace trim edge. Lightweight cotton blend with a hint of stretch. Perfect for adding a feminine touch to flats and sneakers.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'white', 'price' => 9, 'stock' => 80],
                    ['color' => 'black', 'price' => 9, 'stock' => 70],
                    ['color' => 'blue', 'price' => 9, 'stock' => 45],
                ],
            ], $womenSocks],
            // ─────────────────────────────────────────────
            // KIDS: Socks
            // ─────────────────────────────────────────────
            [[
                'name' => 'Colorful Crew Socks', 'brand' => 'Little Steps',
                'description' => 'Fun and colorful crew-length socks for kids. Soft combed cotton blend with reinforced heel and toe. Bright patterns that kids love.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'white', 'price' => 7, 'stock' => 70],
                    ['color' => 'blue', 'price' => 7, 'stock' => 60],
                    ['color' => 'black', 'price' => 7, 'stock' => 50],
                ],
            ], $kidsSocks],
            [[
                'name' => 'Animal Print Ankle Socks', 'brand' => 'Little Steps',
                'description' => 'Adorable ankle socks with cute animal prints. Soft and stretchy cotton blend with non-slip silicone grips on the sole. Machine washable.',
                'is_featured' => true, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'white', 'price' => 8, 'stock' => 65],
                    ['color' => 'gray', 'price' => 8, 'stock' => 55],
                    ['color' => 'brown', 'price' => 8, 'stock' => 40],
                ],
            ], $kidsSocks],
            [[
                'name' => 'School Uniform Socks', 'brand' => 'Tidy Toes',
                'description' => 'Classic uniform socks in durable cotton-polyester blend. Reinforced toe and heel for school-day durability. Ribbed cuff stays in place all day.',
                'is_featured' => false, 'is_new_arrival' => false,
                'variants' => [
                    ['color' => 'white', 'price' => 6, 'stock' => 90],
                    ['color' => 'black', 'price' => 6, 'stock' => 85],
                    ['color' => 'gray', 'price' => 6, 'stock' => 60],
                ],
            ], $kidsSocks],
            [[
                'name' => 'Sport Gripper Socks', 'brand' => 'Active Sole',
                'description' => 'Performance sport socks for active kids. Moisture-wicking fabric with cushioned sole and arch support. Non-slip gripper dots on the bottom.',
                'is_featured' => false, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'white', 'price' => 9, 'stock' => 60],
                    ['color' => 'blue', 'price' => 9, 'stock' => 50],
                    ['color' => 'black', 'price' => 9, 'stock' => 55],
                ],
            ], $kidsSocks],
            [[
                'name' => 'Fuzzy Bed Socks', 'brand' => 'Snug Studio',
                'description' => 'Ultra-soft fuzzy bed socks for cozy nights at home. Plush interior with anti-slip silicone dots on the sole. Fun pastel colors kids adore.',
                'is_featured' => true, 'is_new_arrival' => true,
                'variants' => [
                    ['color' => 'white', 'price' => 10, 'stock' => 45],
                    ['color' => 'blue', 'price' => 10, 'stock' => 40],
                    ['color' => 'gray', 'price' => 10, 'stock' => 35],
                ],
            ], $kidsSocks],

        ];

        foreach ($dataset as [$item, $category]) {
            if ($this->productCount >= self::MAX_PRODUCTS) {
                break;
            }

            $product = Product::create([
                'name' => $item['name'],
                'slug' => $slug($item['name']),
                'description' => $item['description'],
                'brand' => $item['brand'],
                'category_id' => $category->id,
                'status' => true,
                'is_featured' => $item['is_featured'] ?? false,
                'is_new_arrival' => $item['is_new_arrival'] ?? false,
            ]);

            $this->productCount++;

            foreach ($item['variants'] as $variant) {
                $color = $variant['color'];
                if (!in_array($color, self::ALLOWED_COLORS, true)) {
                    continue;
                }

                ProductVariant::create([
                    'product_id' => $product->id,
                    'color' => $color,
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                ]);
            }

            $isFirst = true;
            $imagesAdded = 0;
            for ($i = 1; $i <= 2; $i++) {
                $imagePath = "products/{$product->slug}/{$i}.jpg";
                if (Storage::disk('public')->exists($imagePath)) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_primary' => $isFirst,
                    ]);
                    $isFirst = false;
                    $imagesAdded++;
                }
            }

            $this->command->info("  [{$this->productCount}/{$this->productCount}] {$product->name} — {$imagesAdded} images");
        }

    }
}
