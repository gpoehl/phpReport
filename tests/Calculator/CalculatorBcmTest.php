<?php

declare(strict_types=1);

/**
 * Unit test of Calculator class
 */
use gpoehl\phpReport\Calculator\CalculatorBcm;
use PHPUnit\Framework\TestCase;

class CalculatorBcmTest extends TestCase
{

    private $stack;
    private $values = [70, 10/3, 4.5, '5.5', 0, null];

    public function setUp(): void {
        $this->stack = new CalculatorBcm(2);
        $this->stack->initialize(fn($val) => $val ??= 0, 2);
    }

    public function testAddAndCumulate() {
        foreach ($this->values as $value) {
            $this->stack->add($value);
        }
        $this->stack->cumulateToNextLevel(2);

        $this->assertEquals('83.33', $this->stack->sum(1));
        $this->assertSame(6, $this->stack->count(1));
        $this->assertSame(5, $this->stack->countNN(1));
        $this->assertSame(4, $this->stack->countNZ(1));

        $this->assertEquals('0.00', $this->stack->sum(2));
        $this->assertSame(0, $this->stack->count(2));
        $this->assertSame(0, $this->stack->countNN(2));
        $this->assertSame(0, $this->stack->countNZ(2));

        // bcMath truncates decimals instead of rounding.
        $this->stack->add(2 * 10 / 3);
        $this->assertEquals('6.66', $this->stack->sum(2));

    }
     public function testSub() {
        foreach ($this->values as $value) {
            $this->stack->sub($value);
        }
        $this->assertEquals('-83.33', $this->stack->sum());
        $this->assertSame(6, $this->stack->count());
        $this->assertSame(5, $this->stack->countNN());
        $this->assertSame(4, $this->stack->countNZ());
        $this->assertEquals('-13.88', $this->stack->avg());
        $this->assertEquals('-16.66', $this->stack->avgNN());
        $this->assertEquals('-20.83', $this->stack->avgNZ());
    }

      // Cumulation only till maxLevel
    public function testCumulateNotExistingLevel() {
        $amount = 10 / 3;
        $this->stack->add($amount);
        $this->assertSame('3.33', $this->stack->sum(2));
        // Level far above maxLevel.
        $this->stack->cumulateToNextLevel(10);
        $this->assertSame('3.33', $this->stack->sum(2));
        $this->assertSame('0.00', $this->stack->sum(9));
    }


    /**
     * Value is zero when level doesn't exist.
     */
    public function testMethodsOnNotExistingLevel() {
        $this->stack->add(10);
        $this->assertSame('0.00', $this->stack->sum(99));
        $this->assertSame(0, $this->stack->count(99));
        $this->assertSame(0, $this->stack->countNN(99));
        $this->assertSame(0, $this->stack->countNZ(99));
        $this->assertEquals(null, $this->stack->avg(99));
        $this->assertEquals(null, $this->stack->avgNN(99));
        $this->assertEquals(null, $this->stack->avgNZ(99));
    }



}
