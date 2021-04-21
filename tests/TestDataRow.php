<?php


declare(strict_types=1);

/**
 * Unit test of ObjectGetterFactory class and related getter methods.
 */

class DataRow
{

    public function __construct(public int $productID = 123,
            public string $productName = 'Best Product',
            public int $length = 3,
            public int $width = 4) {
        
    }

    public function getArea() {
        return $this->length * $this->width;
    }

    public function getWeight(int $height, int $specificWeight = 1) {
        return $this->getArea() * $height * $specificWeight;
    }

    public static function staticAdd(... $val) {
        return array_sum($val);
    }

}
