<?php

declare(strict_types=1);

/**
 * Unit test of CalculatorXL class
 */
use gpoehl\phpReport\Calculator\CalculatorXl;
use PHPUnit\Framework\TestCase;

class CalculatorXLTest extends TestCase
{

    private $stack;
    private $values = [70, 40, 4.5, '5.5', 0, null];

    public function setUp(): void {
        $this->stack = new CalculatorXL();
        $this->stack->initialize(fn($val) => $val ??= 0, 2);
    }

    public function testAddAndCumulate() {
        foreach ($this->values as $value) {
            $this->stack->add($value);
        }
        $this->stack->cumulateToNextLevel(2);

        $this->assertEquals(120, $this->stack->sum(1));
        $this->assertSame(6, $this->stack->count(1));
        $this->assertSame(5, $this->stack->countNN(1));
        $this->assertSame(4, $this->stack->countNZ(1));
        $this->assertSame(0, $this->stack->min(1));
        $this->assertSame(70, $this->stack->max(1));

        $this->assertEquals(0, $this->stack->sum(2));
        $this->assertSame(0, $this->stack->count(2));
        $this->assertSame(0, $this->stack->countNN(2));
        $this->assertSame(0, $this->stack->countNZ(2));
        $this->assertSame(null, $this->stack->min(2));
        $this->assertSame(null, $this->stack->max(2));
    }

     public function testSub() {
        foreach ($this->values as $value) {
            $this->stack->sub($value);
        }
        $this->assertEquals(-120, $this->stack->sum());
        $this->assertSame(6, $this->stack->count());
        $this->assertSame(5, $this->stack->countNN());
        $this->assertSame(4, $this->stack->countNZ());
        $this->assertEquals(-20, $this->stack->avg());
        $this->assertEquals(-24, $this->stack->avgNN());
        $this->assertEquals(-30, $this->stack->avgNZ());
        $this->assertSame(-70, $this->stack->min(2));
        $this->assertSame(0, $this->stack->max(2));
        $this->assertSame(-70, $this->stack->min());
        $this->assertSame(0, $this->stack->max());
    }

    public function testMinMax() {
        $this->assertSame(null, $this->stack->min());
        $this->assertSame(null, $this->stack->max());
        $this->stack->add(2);
        $this->stack->add(1);
        $this->stack->add(10);
        $this->assertSame(1, $this->stack->min(2));
        $this->assertSame(10, $this->stack->max(2));
        $this->stack->cumulateToNextLevel(2);
        $this->stack->add(2);
        $this->stack->add(0);
        $this->stack->add(null);
        $this->assertSame(0, $this->stack->min(2));
        $this->assertSame(2, $this->stack->max(2));
        $this->assertSame(0, $this->stack->min(1));
        $this->assertSame(10, $this->stack->max(1));

        $this->stack->cumulateToNextLevel(2);
        // Level 2 has no entries.
        $this->stack->add(null);
        $this->assertSame(null, $this->stack->min(2));
        $this->assertSame(null, $this->stack->max(2));
        $this->stack->cumulateToNextLevel(2);
        $this->assertSame(15, $this->stack->sum(1));
        $this->assertSame(0, $this->stack->min(1));
        $this->assertSame(10, $this->stack->max(1));
    }

}
