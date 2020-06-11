<?php

declare(strict_types=1);

/**
 * Unit test of ObjectGetterFactory class and related getter methods.
 */
use gpoehl\phpReport\getter\GetterFactory;
use PHPUnit\Framework\TestCase;

class GetterFactoryForObjectTest extends TestCase {

    public $stack;

    public function setUp(): void {
        $this->stack = new GetterFactory(true, $this->getTargetClass());
        $this->row = $this->getDataRow();
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
            'object property' => [123, 'productID'],
            'closure' => [12, fn($row, $rowKey) => ($row->length * $row->width)],
            'default method' => [12, ['getArea']],
            'object method' => ['objName', [new GetterFactoryForObject(), 'getName']],
            'object method 1 param' => ['OBJNAME', [new GetterFactoryForObject(), 'getName'], true],
            'object method 2 params' => [str_split('objName'), [new GetterFactoryForObject(), 'getName'], false, true],
            'static class method' => [60, ['GetterFactoryForObject', 'getVolume']],
            'static class method with params' => [150, ['GetterFactoryForObject', 'getWeight'], 2, 10, 20],
        ];
    }
    
     public function testGetInvalidGetterSource() {
        $this->expectException(InvalidArgumentException::class);
        $getter = $this->stack->getGetter(['A','B','C'],null);
    }
    
    /**
     * @dataProvider sheetProvider
     */
     public function testGetValueForSheet($expected, $key, $source, ...$params) {
        $getter = $this->stack->getSheetGetter($key, $source, $params);
        $this->assertSame($expected, $getter->getValue($this->row, 2));
    }
    
    public function sheetProvider(){
        return [
             'No source getter' => [[123=>3], fn($row, $rowKey) => [$row->productID => $row->length], null],
             'Source getter' => [[123=>3], 'productID' , 'length'],
        ];
    }

    public function getTargetClass() {
        return new class() {

            public function getArea($row, $rowKey) {
                return $row->length * $row->width;
            }

            public function getVolume($row, $rowKey) {
                return $this->getArea($row, $rowKey) * $row->height;
            }

            public static function getWeight($row, $rowKey, $weightPerUnit, $tareWeight = 0, $extraWeight = 0) {
                return $this->getVolume($row, $rowKey) * $weightPerUnit + $tareWeight + $extraWeight;
            }
        };
    }

    public function getDataRow() {
        return new class() {

            public int $productID = 123;
            public int $length = 3;
            public int $width = 4;
            public int $height = 5;

            public function getArea() {
                return $this->length * $this->width;
            }

            public function getWeight($weightPerUnit, $tareWeight = 0, $extraWeight = 0) {
                return $this->getArea() * $this->height * $weightPerUnit + $tareWeight + $extraWeight;
            }
        };
    }

}

class GetterFactoryForObject {

    private $name = 'objName';

    public function getName($row, $rowKey, $asUppercase = false, $asArray = false) {
        $name = ($asUppercase) ? strtoupper($this->name) : $this->name;
        return ($asArray) ? str_split($name) : $name;
    }

    public static function getVolume($row, $rowKey) {
        return $row->length * $row->width * $row->height;
    }

    public static function getWeight($row, $rowKey, $weightPerUnit, $tareWeight = 0, $extraWeight = 0) {
        return self::getVolume($row, $rowKey) * $weightPerUnit + $tareWeight + $extraWeight;
    }

}
