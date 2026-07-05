<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;







class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    Log::info($request->query());

    $products = Product::query()
        ->with(['primaryImage', 'images', 'variants', 'category.subCategories'])
        ->when($request->search, fn($q) =>
            $q->where('name', 'like', '%' . $request->search . '%')
        )
        ->when($request->category, fn($q) =>
            $q->whereHas('category', fn($q) =>
                $q->where('slug', $request->category)
                  ->orWhereHas('parent', fn($q) =>
                      $q->where('slug', $request->category)
                  )
            )
        )
        ->when($request->filled('min_price') || $request->filled('max_price') || $request->filled('size'),
            function ($q) use ($request) {

              $q->whereHas('variants', function($q2) use ($request){

              if($request->filled('min_price')){

              $q2->where('price', '>=', $request->min_price);
              }
              if($request->filled('max_price')){

              $q2->where('price', '<=', $request->max_price);
              }
              if($request->filled('size')){

              $q2->where('size', $request->size);
              }

              });

            })

        ->when($request->is_featured, fn($q) =>
            $q->where('is_featured', true)
        )
        ->when($request->is_new_arrival, fn($q) =>
            $q->where('is_new_arrival', true)
        )

        ->when($request->sort === 'price_asc', function ($q) {
            $q->orderBy(
                \DB::raw('(SELECT MIN(price) FROM product_variants WHERE product_id = products.id)')
            );
        })
        ->when($request->sort === 'price_desc', function ($q) {
            $q->orderBy(
                DB::raw('(SELECT MIN(price) FROM product_variants WHERE product_id = products.id)'),
                'desc'
            );
        })
        ->unless($request->sort || $request->is_featured || $request->is_new_arrival, function ($q) {
            $q->inRandomOrder(42);
        })
        ->paginate(min($request->integer('per_page', 6), 50));

    return response()->json([
        'status'  => true,
        'message' => 'Products retrieved successfully',
        'data'    => $products,
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

       $existingProduct = Product::where('name' , $request->name)
       ->where('brand' , $request->brand)
       ->where('category_id' , $request->category_id)
       ->first();

       if($existingProduct){
         return response()->json([
        'status' => false,
        'message' => 'This product already exists',
        'data' => $existingProduct
    ], 409);
       }
        //  create product
       $product = Product::create([
        'name'           => $request->name,
        'brand'          => $request->brand,
        'category_id'    => $request->category_id,
        'slug'           => Str::slug($request->name) . '-' . uniqid(),
        'description'    => $request->description,
        'is_featured'    => $request->is_featured ?? false,
        'is_new_arrival' => $request->is_new_arrival ?? false,
        ]



    );

        //  store images & primary image

        if($request->hasFile('images') ){

        foreach($request->file('images') as $index => $image){

        $product->images()->create([
            'image_path' => $image->store('products','public'),
            'is_primary' => $index === 0

        ]);

        }
        }

        //  store variants

        foreach($request->variants as $variant){

          $size = strtoupper($variant['size']);
          $color = strtolower($variant['color']);

          $exists = $product->variants()
          ->where('size' ,$size)
          ->where('color' ,$color)
          ->exists();

          if($exists){
            // that's mean Skip this variant
            continue;

          }
          $product->variants()->create([
             'size'  => $size,
            'color' => $color,
            'price' => (float) $variant['price'],
            'stock' => (int) $variant['stock'],
          ]);
        }

        return response()->json([

        'status' => true,
        'message' => 'product created successfully',
        'data'=> $product->load(['images', 'variants' , 'category.subCategories'])

        ]);



    }

    /**
     * Display the specified resource.
     */
   public function show(Product $product)
{

    $product =  Cache::remember("product:{$product->id}", now()->addDay(), function ( ) use ($product){

       return$product->load(['primaryImage', 'images', 'variants', 'category.subCategories']);


    });

    return response()->json([
        'status'  => true,
        'message' => 'Product retrieved successfully',
        'data'    => $product,
    ]);
}
    private function decodeTranslatable($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && (isset($decoded['en']) || isset($decoded['ar']))) {
                return $decoded;
            }
        }
        return $value;
    }

    private function translatableInput(Request $request, string $field): array|null
    {
        $value = $request->input($field);
        if ($value !== null) {
            $decoded = $this->decodeTranslatable($value);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        $en = $request->input($field . '_en');
        $ar = $request->input($field . '_ar');
        if ($en !== null || $ar !== null) {
            $result = [];
            if ($en !== null) $result['en'] = $en;
            if ($ar !== null) $result['ar'] = $ar;
            return $result;
        }
        return null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = [];

        foreach (['slug', 'category_id', 'is_featured', 'is_new_arrival'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        $translations = $product->translations ?? [];

        foreach (['name', 'description', 'brand'] as $field) {
            $translatable = $this->translatableInput($request, $field);
            if ($translatable !== null) {
                foreach ($translatable as $locale => $value) {
                    $translations[$field][$locale] = $value;
                }
                $data[$field] = $translatable[app()->getLocale()] ?? $translatable['en'] ?? '';
            }
        }

        $data['translations'] = $translations;

        $product->update($data);

         if($request->hasFile('images') ){

        foreach($request->file('images') as $index => $image){

        $product->images()->create([
            'image_path' => $image->store('products','public'),
            'is_primary' => $index === 0

        ]);

        }
        }


         if($request->has('variants')){

        foreach($request->variants as $variant){

            $colorEn = $variant['color_en'] ?? null;
            $colorAr = $variant['color_ar'] ?? null;
            $colorJson = $variant['color'] ?? null;

            $colorArr = null;
            if ($colorEn !== null || $colorAr !== null) {
                $colorArr = [];
                if ($colorEn !== null) $colorArr['en'] = $colorEn;
                if ($colorAr !== null) $colorArr['ar'] = $colorAr;
            } elseif ($colorJson !== null) {
                $decoded = $this->decodeTranslatable($colorJson);
                $colorArr = is_array($decoded) ? $decoded : ['en' => $colorJson];
            }

            $variantData = [
                'price' => (float) $variant['price'],
                'stock' => (int) $variant['stock'],
            ];

            if ($colorArr !== null) {
                $variantData['color'] = $colorArr[app()->getLocale()] ?? $colorArr['en'] ?? '';
                $variantData['translations'] = ['color' => $colorArr];
            }

            $product->variants()->updateOrCreate(
                isset($variant['id']) && $variant['id']
                    ? ['id' => $variant['id']]
                    : ['price' => $variant['price'], 'stock' => $variant['stock']],
                $variantData
            );
        }
        }

        Cache::forget("product:{$product->id}");

        $product->refresh();

        return response()->json([
        'status'  => true,
        'message' => 'Product updated successfully',
        'data'    => $product->load(['images', 'variants', 'category.subCategories']),
        'debug'   => [
            'translations'       => $product->translations,
        ],
    ]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //

        foreach($product->images as $image){

        Storage::disk('public')->delete($image->image_path);



        }

        $product->delete();
        Cache::forget("product:{$product->id}");

          return response()->json([
        'status'  => true,
        'message' => 'Product deleted successfully',
    ]);
    }
}
