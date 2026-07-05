<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $categories = Cache::remember('categories.tree', now()->addDay(), function () {

        //  \Log::info('DB HIT - categories loaded from MySQL');

                    return   Category::with('subCategories')
                    ->whereNull('parent_id')
                    ->get();

    });

    return response()->json([
        'status' => true,
        'data'   => $categories
    ]);


    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $name = $this->translatableInput($request, 'name');
    $nameEn = is_array($name) ? ($name['en'] ?? '') : ($name ?? '');

    $request->validate([
        'slug' => 'nullable|string|max:255|unique:categories,slug',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'parent_id' => 'nullable|integer|exists:categories,id',
    ]);

    $slug = $request->slug ?? \Illuminate\Support\Str::slug($nameEn);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('categories', 'public');
    }

    $translations = [];

    if ($name !== null) {
        foreach ($name as $locale => $value) {
            $translations['name'][$locale] = $value;
        }
    }

    $category = Category::create([
        'name' => $nameEn,
        'slug' => $slug,
        'image' => $imagePath,
        'parent_id' => $request->parent_id ?? null,
        'translations' => $translations,
    ]);

    Cache::forget('categories.tree');

    return response()->json([
        'message' => 'Category created successfully',
        'data' => $category
    ], 201);
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
    public function update(Request $request, Category $category)
    {
        $name = $this->translatableInput($request, 'name');
        $nameEn = is_array($name) ? ($name['en'] ?? '') : ($name ?? '');

        $request->validate([
            'name' => 'sometimes',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        $slug = $request->slug ?? Str::slug($nameEn);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->image = $imagePath;
        }

        $data = [
            'slug' => $slug,
            'parent_id' => $request->parent_id ?? null,
        ];

        $translations = $category->translations ?? [];

        if ($name !== null) {
            foreach ($name as $locale => $value) {
                $translations['name'][$locale] = $value;
            }
            $data['name'] = $name[app()->getLocale()] ?? $name['en'] ?? '';
            $data['translations'] = $translations;
        }

        $category->update($data);

        Cache::forget('categories.tree');


        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
         $category->delete();

        Cache::forget('categories.tree');


    return response()->json([
        'message' => 'Category deleted successfully'
    ], 200);
    }
}
