<?php

declare(strict_types=1);

/**
 * Unit test of ObjectGetterFactory class and related getter methods.
 */
use gpoehl\phpReport\getter\GetterFactory;
use PHPUnit\Framework\TestCase;

class GetterFactoryForArrayTest extends TestCase {

    public $stack;

    public function setUp(): void {
        $this->stack = new GetterFactory(false, $this->getTargetClass());
        $this->row = [999, 'productID' => 123, 'length' => 3, 'width' => 4, 'height' => 5];
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
            'integer key' => [999, 0],
            'array key' => [123, 'productID'],
            'closure' => [12, fn($row, $rowKey) => ($row['length'] * $row['width'])],
            'default method' => [12, ['getArea']],
            'object method' => ['arrName', [new GetterFactoryForArrayArray(), 'getName']],
            'object method 1 param' => ['ARRNAME', [new GetterFactoryForArrayArray(), 'getName'], true],
            'object method 2 params' => [str_split('arrName'), [new GetterFactoryForArrayArray(), 'getName'], false, true],
            'static class method' => [60, ['GetterFactoryForArrayArray', 'getVolume']],
            'static class method with params' => [150, ['GetterFactoryForArrayArray', 'getWeight'], 2, 10, 20],
        ];
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

    public function getTargetClass() {
        return new class() {

            public function getArea($row, $rowKey) {
                return $row['length'] * $row['width'];
            }

            public static function getWeight($row, $rowKey, $weightPerUnit, $tareWeight = 0, $extraWeight = 0) {
                return $this->Area($row, $rowKey) * $row['height'] * $weightPerUnit + $tareWeight + $extraWeight;
            }
        };
    }

}

class GetterFactoryForArrayArray {

    private $name = 'arrName';

    public function getName($row, $rowKey, $asUppercase = false, $asArray = false) {
        $name = ($asUppercase) ? strtoupper($this->name) : $this->name;
        return ($asArray) ? str_split($name) : $name;
    }

    public static function getVolume($row, $rowKey) {
        return $row['length'] * $row['width'] * $row['height'];
    }

    public static function getWeight($row, $rowKey, $weightPerUnit, $tareWeight = 0, $extraWeight = 0) {
        return self::getVolume($row, $rowKey) * $weightPerUnit + $tareWeight + $extraWeight;
    }

}
