<?php

namespace KanyJoz\AniMerged;

readonly class Configuration
{
    public function __construct(private array $configs) {}

    public function get(string $name, mixed $default = null): mixed
    {
        $path  = explode('.', $name);
        $value = $this->configs[array_shift($path)] ?? null;

        if ($value === null) {
            return $default;
        }

        foreach ($path  as $key) {
            if (! isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        return $value;
    }
}
