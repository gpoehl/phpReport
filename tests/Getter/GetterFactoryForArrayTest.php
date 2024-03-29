<?php

declare(strict_types=1);

/**
 * Unit test of ObjectGetterFactory class and related getter methods.
 */
require_once __DIR__ . '/../Foo.php';

use gpoehl\phpReport\Getter\GetArrayItem;
use gpoehl\phpReport\Getter\GetterFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GetterFactoryForArrayTest extends TestCase {

    public $row = ['a', 'b', 'name' => 'nobody', 'x y' => 'key has blank'];
    public $stack;

    public function setUp(): void {
        $this->stack = new GetterFactory($this->row);
    }

    #[DataProvider('sourceProvider')]
    public function testGetValue($expected, $source, ...$params): void {
        $getter = $this->stack->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue($this->row, 2));
    }

    public static function sourceProvider(): array {
        return [
            'base getter offset' => ['b', new GetArrayItem(1)],
            'base getter assoc' => ['nobody', new GetArrayItem('name')],
            'arr item' => ['nobody', 'name'],
            'arr item blank in key' => ['key has blank', 'x y'],
            'arr item index' => ['b', 1],
        ];
    }

    public function testUnknownArrayKeyThrowsException(): void {
      $this->expectException(gpoehl\phpReport\UnknownPropertyException::class);
        $this->expectExceptionMessage("Unknown array key 'xxx'.");

        $getter = $this->stack->getGetter('xxx', []);
        $getter->getValue($this->row);
    }

    #[DataProvider('stringProvider')]
    public function testGetValueFromString($expected, $source, ...$params): void {
        $getter = $this->stack->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue('3,5,7', null));
        $this->assertSame($expected, $getter->getValue('3,5,7'));
    }

    public static function stringProvider(): array {
        return [
            ['5', fn($row, $rowKey, $start, $length) => substr($row, $start, $length), 2, 1],
            ['3,5', fn($row, $rowKey, $start, $length) => substr($row, $start, $length), 0, 3],
        ];
    }
}
