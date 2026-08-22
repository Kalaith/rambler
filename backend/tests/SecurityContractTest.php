<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SecurityContractTest extends TestCase
{
    public function testDatabaseInitializationIsNotExposedAsAnHttpRoute(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/public/index.php');
        self::assertIsString($source);
        self::assertStringNotContainsString("'/db-init'", $source);
        self::assertStringNotContainsString("'/db/init'", $source);
    }
}
