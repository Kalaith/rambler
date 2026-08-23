<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Env
{
    public static function required(string $name): string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        $value = is_string($value) ? trim($value) : '';

        if ($value === '') {
            throw new RuntimeException("{$name} environment variable is required.");
        }

        return $value;
    }

    public static function configured(string $name): string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        if (!is_string($value)) {
            throw new RuntimeException("{$name} environment variable is required.");
        }

        return trim($value);
    }
}
