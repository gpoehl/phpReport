<?php

declare(strict_types=1);

/**
 * Unit test of CumulatorXS class
 */

use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class CalculatorXSTest extends TestCase {

    public $mp;
    protected $stack;

    public function setUp(): void {
        $mp = Factory::properties();
        $mp->level = 2;
        $mp->maxLevel = 2;
        $this->mp = $mp;
        $this->stack = Factory::calculator($mp, 2, Report::XS);
    }

    public function testAddValues() {
        $arr = $arr = [
            [2, 2],
            [10, 12],
            [4, 16],
        ];
        foreach ($arr as $parm) {
            list($val, $sum) = $parm;
            $this->stack->add($val);
            $this->assertSame($sum, $this->stack->sum());
            // higer level has same value
            $this->assertSame($sum, $this->stack->sum($this->mp->level - 1));
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

        $this->stack->cumulateToNextLevel($this->mp->level);
        $this->assertSame(0, $this->stack->sum());

        $this->assertSame($amount, $this->stack->sum($this->mp->level - 1));
        $this->stack->add($amount);
        $this->stack->cumulateToNextLevel($this->mp->level);
        $this->assertSame($amount * 2, $this->stack->sum($this->mp->level - 1));
    }

    public function testCumulateNotExistingLevel() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        $this->mp->level = 10;
        $this->stack->cumulateToNextLevel($this->mp->level);
        $this->assertSame($amount, $this->stack->sum(2));
    }

    public function testSumOnNotExistingLevel() {
        $this->stack->add(10);
        $this->assertSame(0, $this->stack->sum(99));
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
