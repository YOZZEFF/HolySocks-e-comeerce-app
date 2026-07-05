<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, Translatable;

    protected array $translatable = ['name', 'description', 'brand'];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'brand',
        'translations',
        'status',
        'is_featured',
        'is_new_arrival',
        'avg_rating',
        'rating_count',
    ];

    protected $casts = [
        'status'         => 'boolean',
        'is_featured'    => 'boolean',
        'is_new_arrival' => 'boolean',
        'avg_rating'     => 'float',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trans('name', original: $value),
            set: function ($value) {
                if (is_array($value)) {
                    foreach ($value as $locale => $v) {
                        $this->setTrans('name', $v, $locale);
                    }
                    return $value[app()->getLocale()] ?? $value['en'] ?? null;
                }
                $this->setTrans('name', $value, app()->getLocale());
                return $value;
            },
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trans('description', original: $value),
            set: function ($value) {
                if (is_array($value)) {
                    foreach ($value as $locale => $v) {
                        $this->setTrans('description', $v, $locale);
                    }
                    return $value[app()->getLocale()] ?? $value['en'] ?? null;
                }
                $this->setTrans('description', $value, app()->getLocale());
                return $value;
            },
        );
    }

    protected function brand(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trans('brand', original: $value),
            set: function ($value) {
                if (is_array($value)) {
                    foreach ($value as $locale => $v) {
                        $this->setTrans('brand', $v, $locale);
                    }
                    return $value[app()->getLocale()] ?? $value['en'] ?? null;
                }
                $this->setTrans('brand', $value, app()->getLocale());
                return $value;
            },
        );
    }


public function getImagePathAttribute(): ?string
{
    return $this->primaryImage?->image_path;
}

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }


     public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

     public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

     public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

public function wishlists(){

return $this->hasMany(Wishlist::class);
}

public function reviews()
{
    return $this->hasMany(Review::class);
}
}
