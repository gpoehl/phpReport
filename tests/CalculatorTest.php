<?php

declare(strict_types=1);

/**
 * Unit test of Cumulator class
 */
use gpoehl\phpReport\Calculator;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{

    private $stack;

    public function setUp(): void {
        $rep = $this->createStub(Report::class);
        $rep->method('getLevel')
                ->will($this->returnCallback(fn($val) => $val ??= 0));
        $this->stack = new Calculator($rep, 2);
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

    public function testCumulate() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame(10, $this->stack->sum(2));
        $this->stack->cumulateToNextLevel(2);
        $this->assertSame($amount, $this->stack->sum(1));
        $this->assertSame(0, $this->stack->sum(2));
        $this->stack->add($amount);
        $this->assertSame(10, $this->stack->sum(2));
        $this->assertSame($amount * 2, $this->stack->sum(1));
        $this->stack->cumulateToNextLevel(2);
        $this->stack->cumulateToNextLevel(1);
        $this->assertSame(0, $this->stack->sum(2));
        $this->assertSame(0, $this->stack->sum(1));
        $this->assertSame($amount * 2, $this->stack->sum(0));
    }

    // Cumulation only till maxLevel
    public function testCumulateNotExistingLevel() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        // Level above maxLevel.
        $this->stack->cumulateToNextLevel(10);
        $this->assertSame($amount, $this->stack->sum(2));
    }

    public function testSumOnNotExistingLevel() {
        $this->stack->add(10);
        $this->assertSame(0, $this->stack->sum(99));
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

}
