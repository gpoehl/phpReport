<?php

declare(strict_types=1);

/**
 * Unit test of FixedSheet class
 */
use gpoehl\phpReport\Calculator\CalculatorXS;
use gpoehl\phpReport\FixedSheet;
use PHPUnit\Framework\TestCase;

class FixedSheetTest extends TestCase
{

    public $stack;

    public function setUp(): void {
        $calc = new CalculatorXS();
        $calc->initialize(fn($val) => $val ??= 0, 2);
        $this->stack = new FixedSheet($calc, 1, 6);
    }

    public function testInstantiate() {
        $this->assertSame(6, count($this->stack->getItems()));
        $this->assertInstanceOf(CalculatorXS::class, $this->stack->getItem(1));
    }

    public function testAddItemViaArrayAccessThrowsException() {
        $this->expectException(Exception::class);
        $this->stack[10] = 55;
    }

    public function testAddThrowsException() {
        $this->expectException(Exception::class);
        $this->stack->add(['NewItem1' => 1]);
    }

}
