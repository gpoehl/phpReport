<?php

declare(strict_types=1);

/**
 * Unit test of Cumulator class
 */

use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase {

    public $mp;
    protected $stack;

    public function setUp(): void {
        $mp = Factory::properties();
        $mp->level = 2;
        $mp->maxLevel = 2;
        $this->mp = $mp;
        $this->stack = Factory::calculator($mp, 2, Report::REGULAR);
    }

    public function testAddValues() {
        $arr =  [
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

    public function testCumulate() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        $this->assertSame($amount, $this->stack->sum(2));

        $this->stack->cumulateToNextLevel();
        $this->assertSame(0, $this->stack->sum());

        $this->assertSame($amount, $this->stack->sum($this->mp->level - 1));
        $this->stack->add($amount);
        $this->stack->cumulateToNextLevel();
        $this->assertSame($amount * 2, $this->stack->sum($this->mp->level - 1));
    }

    public function testCumulateNotExistingLevel() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        $this->mp->level = 10;
        $this->stack->cumulateToNextLevel();
        $this->assertSame($amount, $this->stack->sum(2));
    }

    public function testSumOnNotExistingLevel() {
        $this->stack->add(10);
        $this->assertSame(0, $this->stack->sum(99));
    }

    public function testCounter() {
        $this->assertSame(0, $this->stack->nn());
        $this->assertSame(0, $this->stack->nz());
    }

    public function testMinFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->min();
    }

    public function testMaxFailure() {
        $this->expectExceptionMessage('Call to undefined method');
        $this->stack->max();
    }

    public function testNotNullAndNotZeroCounter() {
        $this->stack->add(2);
        $this->stack->add(10);
        $this->stack->add(4);
        $this->assertSame(16, $this->stack->sum());
        $this->assertSame(3, $this->stack->nn());
        $this->assertSame(3, $this->stack->nz());
        $this->mp->level = 2;
        $this->stack->cumulateToNextLevel();
        // Values set to 0 / null on level 2 after cumulation to next level
        $this->assertSame(0, $this->stack->sum());
        $this->assertSame(0, $this->stack->nn());
        $this->mp->level = 1;
        // Values must be same on level 1 as on level 2 before cumulation 
        $this->assertSame(16, $this->stack->sum());
        $this->assertSame(3, $this->stack->nn());
        $this->assertSame(3, $this->stack->nz());
    }

    public function testZeroAndNullValues() {
        $this->stack->add(2);
        $this->stack->add(0);
        $this->stack->add(null);
        $this->assertSame(2, $this->stack->sum());
        $this->assertSame(2, $this->stack->nn());
        $this->assertSame(1, $this->stack->nz());
        $this->mp->level = 2;
        $this->stack->cumulateToNextLevel();
    }

}
