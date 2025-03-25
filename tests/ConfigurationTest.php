<?php

declare(strict_types=1);

namespace KanyJoz\Tests;

use KanyJoz\AniMerged\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    #[DataProvider('getProvider')]
    public function testGet(
        array $configs,
        mixed $expectedValue,
        string $key,
        mixed $default
    ): void
    {
        $config = new Configuration($configs);

        $this->assertSame($expectedValue, $config->get($key, $default));
    }

    public static function getProvider(): array
    {
        return [
            'returns default if key is not set' => [[], 'default', 'def', 'default'],
            'key set' => [['app' => 'ani-merged'], 'ani-merged', 'app', 'default'],
            'nested key set' => [['db' => ['port' => 8000]], 8000, 'db.port', null],
            // ...
        ];
    }
}