<?php

declare(strict_types=1);

/**
 * Unit test of CumulatorXS class
 */
use gpoehl\phpReport\CalculatorXS;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class CalculatorXSTest extends TestCase
{

    protected $stack;

    public function setUp(): void {
        $rep = $this->createStub(Report::class);
        $rep->method('getLevel')
                ->will($this->returnCallback(fn($val) => $val ??= 0));
        $this->stack = new CalculatorXS($rep, 2);
    }

    public function testAddValues() {
        $arr = [
            [2, 2],
            [10, 12],
            [4, 16],
            ['4.5', 20.5]
        ];
        foreach ($arr as $parm) {
            [$val, $sum] = $parm;
            // Add to maxLevel
            $this->stack->add($val);
            $this->assertSame($sum, $this->stack->sum(2));
            // higer levels have same value
            $this->assertSame($sum, $this->stack->sum(1));
            $this->assertSame($sum, $this->stack->sum());
        }
    }

    public function testIncrement() {
        $this->stack->inc();
        $this->stack->inc();
        $this->assertSame(2, $this->stack->sum());
    }

    public function testCumulate() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        $this->assertSame($amount, $this->stack->sum(2));

        $this->stack->cumulateToNextLevel(2);
        $this->assertSame(0, $this->stack->sum(2));

        $this->assertSame($amount, $this->stack->sum(1));
        $this->stack->add($amount);
        $this->stack->cumulateToNextLevel(2);
        $this->assertSame($amount * 2, $this->stack->sum(1));
    }

    public function testNNCounterFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->nn();
    }

    public function testNZCounterFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->nz();
    }

    public function testMinFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->min();
    }

    public function testMaxFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->max();
    }

}
