<?php

declare(strict_types=1);

namespace HernandoTech\ServiceHealth\Support;

final class ServiceSelection
{
    /** @return list<int> */
    public static function normalize(mixed $value): array
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): int => (int) $id, $values),
            static fn (int $id): bool => $id > 0,
        )));
    }

    /** @param list<int> $serviceIds */
    public static function isExplicit(array $serviceIds): bool
    {
        return $serviceIds !== [];
    }
}
