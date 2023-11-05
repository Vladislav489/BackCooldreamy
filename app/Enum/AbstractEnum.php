<?php

namespace App\Enum;

use Illuminate\Support\Collection;

abstract class AbstractEnum
{
    /**
     * @return Collection
     */
    public static function constants(): Collection
    {
        $reflector = new \ReflectionClass(get_called_class());

        return collect($reflector->getConstants());
    }

    /**
     * @return array
     */
    public static function getList(): array
    {
        $refl = new \ReflectionClass(static::class);
        $items = [];
        foreach ($refl->getConstants() as $rule) {
            $items[] = $rule;
        }

        return $items;
    }
}
