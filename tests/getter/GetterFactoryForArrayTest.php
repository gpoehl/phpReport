<?php

declare(strict_types=1);

/**
 * Unit test of ObjectGetterFactory class and related getter methods.
 */
require_once __DIR__ . '/../Foo.php';

use gpoehl\phpReport\getter\GetArrayItem;
use gpoehl\phpReport\getter\GetterFactory;
use PHPUnit\Framework\TestCase;

class GetterFactoryForArrayTest extends TestCase
{
    public $row = ['a', 'b', 'name' => 'nobody', 'x y' => 'key has blank'];
    public $stack;

    public function setUp(): void {
        $this->stack = new GetterFactory(false);
    }

    /**
     * @dataProvider sourceProvider
     */
    public function testGetValue($expected, $source, ...$params) {
        $getter = $this->stack->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue($this->row, 2));
    }

    public function sourceProvider() {
        return [
            'base getter offset' => ['b', new GetArrayItem(1)],
            'base getter assoc' => ['nobody', new GetArrayItem('name')],
            'arr item' => ['nobody', 'name'],
            'arr item blank in key' => ['key has blank', 'x y'],
            'arr item index' => ['b', 1],
        ];
    }

    public function testNotFoundWarning() {
        $this->expectWarning();
        $this->expectWarningMessageMatches('/^Undefined array key .*"x x x"$/');
        $getter = $this->stack->getGetter('x x x', []);
        $getter->getValue($this->row);
    }

    // Suppress warning by setting isJoin in getter factory
    public function testSuppressWarning() {
        $this->stack->isJoin = true;
        $getter = $this->stack->getGetter('x x x', []);
        $this->assertSame(Null, $getter->getValue($this->row));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetValueFromString($expected, $source, ...$params) {
        $getter = $this->stack->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue('3,5,7', null));
        $this->assertSame($expected, $getter->getValue('3,5,7'));
    }

    public function stringProvider() {
        return [
            ['5', fn($row, $rowKey, $start, $length) => substr($row, $start, $length), 2, 1],
            ['3,5', fn($row, $rowKey, $start, $length) => substr($row, $start, $length), 0, 3],
        ];
    }

}
