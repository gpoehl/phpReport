<?php

declare(strict_types=1);

/**
 * Unit test of CalculatorXS class
 */
use gpoehl\phpReport\Calculator\CalculatorXS;
use PHPUnit\Framework\TestCase;

class CalculatorXSTest extends TestCase
{

    protected $stack;
    private $values = [70, 40, 4.5, '5.5', 0, null];

    public function setUp(): void {
        $this->stack = new CalculatorXS();
        $this->stack->initialize(fn($val) => $val ??= 0, 2);
    }

    public function testAddAndCumulate() {
        foreach ($this->values as $value) {
            $this->stack->add($value);
        }
        $this->stack->cumulateToNextLevel(2);
        $this->assertEquals(120, $this->stack->sum(1));
        $this->assertEquals(0, $this->stack->sum(2));
         $this->assertEquals(0, $this->stack->sum(99));
    }

    public function testSub() {
        foreach ($this->values as $value) {
            $this->stack->sub($value);
        }
        $this->assertEquals(-120, $this->stack->sum(2));
    }

    public function testInc() {
        $this->stack->inc();
        $this->stack->inc();
        $this->assertSame(2, $this->stack->sum(2));
        $this->assertSame(2, $this->stack->sum(1));
        $this->assertSame(2, $this->stack->sum(0));
    }

}
