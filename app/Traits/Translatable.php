<?php

namespace App\Traits;

trait Translatable
{
    public function initializeTranslatable()
    {
        $this->mergeCasts(['translations' => 'array']);
    }

    public function trans(string $field, mixed $original = null, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $translations = $this->translations ?? [];
        return $translations[$field][$locale]
            ?? $translations[$field]['en']
            ?? $original;
    }

    public function setTrans(string $field, mixed $value, ?string $locale = null): static
    {
        $locale ??= app()->getLocale();
        $translations = $this->translations ?? [];
        $translations[$field][$locale] = $value;
        $this->translations = $translations;
        return $this;
    }
}
