<?php

declare(strict_types=1);

/**
 * Unit test of CalculatorXS class
 */
use gpoehl\phpReport\Calculator\CalculatorBcmXS;
use PHPUnit\Framework\TestCase;

class CalculatorBcmXSTest extends TestCase
{

    protected $stack;
    private $values = [70, 10 / 3, 4.5, '5.5', 0, null];

    public function setUp(): void {
        $this->stack = new CalculatorBcmXS();
        $this->stack->initialize(fn($val) => $val ??= 0, 2);
    }

    public function testAddAndCumulate() {
        foreach ($this->values as $value) {
            $this->stack->add($value);
        }
        $this->stack->cumulateToNextLevel(2);
        $this->assertEquals('83.33', $this->stack->sum(1));
        $this->assertEquals('0.00', $this->stack->sum(2));
        // bcMath truncates decimals instead of rounding.
        $this->stack->add(2 * 10 / 3);
        $this->assertEquals('6.66', $this->stack->sum(2));
    }

    public function testSub() {
        foreach ($this->values as $value) {
            $this->stack->sub($value);
        }
        $this->assertEquals('-83.33', $this->stack->sum(2));
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

    public function testNullScale() {
        $calc = new CalculatorBcmXS(null);
        $calc->initialize(fn($val) => $val ??= 0, 0);
        $calc->add(10 / 3);
        $this->assertSame('3', $calc->sum());
    }

    public function testDefaultScale() {
        bcscale(5);
        $calc = new CalculatorBcmXS();
        $calc->initialize(fn($val) => $val ??= 0, 0);
        $calc->add(10 / 3);
        $this->assertSame('3.33', $calc->sum());
    }

    public function testDefaultBcScale() {
        bcscale(5);
        $calc = new CalculatorBcmXS(null);
        $calc->initialize(fn($val) => $val ??= 0, 0);
        $calc->add(10 / 3);
        $this->assertSame('3.33333', $calc->sum());
    }

}
