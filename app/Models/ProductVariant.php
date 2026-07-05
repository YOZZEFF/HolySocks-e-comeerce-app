<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use Translatable;

    protected array $translatable = ['color'];

    protected $fillable = [
        'product_id',
        'color',
        'price',
        'stock',
        'translations',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trans('color', original: $value),
            set: function ($value) {
                if (is_array($value)) {
                    foreach ($value as $locale => $v) {
                        $this->setTrans('color', $v, $locale);
                    }
                    return $value[app()->getLocale()] ?? $value['en'] ?? null;
                }
                $this->setTrans('color', $value, app()->getLocale());
                return $value;
            },
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
