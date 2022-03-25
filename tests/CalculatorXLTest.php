<?php

declare(strict_types=1);

/**
 * Unit test of CumulatorXL class
 */
use gpoehl\phpReport\CalculatorXL;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class CalculatorTestXL extends TestCase
{

    private $stack;

    public function setUp(): void {
        $rep = $this->createStub(Report::class);
        $rep->method('getLevel')
                ->will($this->returnCallback(fn($val) => $val ??= 0));
        $this->stack = new CalculatorXL($rep, 2);
    }

    public function testMinMax() {
        $this->assertSame(null, $this->stack->min());
        $this->assertSame(null, $this->stack->max());
    }

    public function testNotNullAndNotZeroCounter() {
        $this->stack->add(2);
        $this->stack->add(10);
        $this->stack->add(4);
        $this->assertSame(16, $this->stack->sum(2));
        $this->assertSame(3, $this->stack->nn(2));
        $this->assertSame(3, $this->stack->nz(2));
        $this->stack->cumulateToNextLevel(2);
        // Values set to 0 / null on level 2 after cumulation to next level
        $this->assertSame(0, $this->stack->sum(2));
        $this->assertSame(0, $this->stack->nn(2));
        $this->assertSame(0, $this->stack->nz(2));
        // Values must be same on level 1 as on level 2 before cumulation
        $this->assertSame(16, $this->stack->sum(1));
        $this->assertSame(3, $this->stack->nn(1));
        $this->assertSame(3, $this->stack->nz(1));
    }

    public function testZeroAndNullValues() {
        $this->stack->add(2);
        $this->stack->add(0);
        $this->stack->add(null);
        $this->assertSame(2, $this->stack->sum());
        $this->assertSame(2, $this->stack->nn());
        $this->assertSame(1, $this->stack->nz());
    }

    public function testMinMaxValues() {
        $this->stack->add(2);
        $this->stack->add(1);
        $this->stack->add(10);
        $this->assertSame(13, $this->stack->sum(2));
        $this->assertSame(1, $this->stack->min(2));
        $this->assertSame(10, $this->stack->max(2));
        $this->stack->cumulateToNextLevel(2);
        $this->stack->add(2);
        $this->stack->add(0);
        $this->stack->add(null);
        $this->assertSame(2, $this->stack->sum(2));
        $this->assertSame(0, $this->stack->min(2));
        $this->assertSame(2, $this->stack->max(2));
        $this->stack->cumulateToNextLevel(2);
        $this->stack->add(null);
        $this->assertSame(0, $this->stack->sum(2));
        $this->assertSame(null, $this->stack->min(2));
        $this->assertSame(null, $this->stack->max(2));
        $this->stack->cumulateToNextLevel(2);
        $this->assertSame(15, $this->stack->sum(1));
        $this->assertSame(0, $this->stack->min(1));
        $this->assertSame(10, $this->stack->max(1));
    }

}
