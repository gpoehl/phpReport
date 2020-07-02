<?php

declare(strict_types=1);

/**
 * Unit test of FixedSheet class
 */
use gpoehl\phpReport\CalculatorXS;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class FixedSheetTest extends TestCase {

    public $b;

    public function setUp(): void {
        $mp = Factory::properties();
        $this->b = Factory::sheet($mp, 3, Report::XS, 1, 6);
    }

    public function testInstantiate() {
        $this->assertSame(6, count($this->b->getItems()));
        $this->assertInstanceOf(CalculatorXS::class, $this->b->getItem(1));
    }

    public function testAddItemViaArrayAccessThrowsException() {
        $this->expectException(Exception::class);
        $this->b[10] = 55;
    }

    public function testAddThrowsException() {
        $this->expectException(Exception::class);
        $this->b->add(['NewItem1' => 1]);
    }

}
