<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use HasFactory, Translatable;

    protected array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'image',
        'parent_id',
        'translations',
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



    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
