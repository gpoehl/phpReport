<?php

declare(strict_types=1);

/**
 * Unit test of Cumulator
 * @author Guenter
 */
use gpoehl\backbone\Factory;
use PHPUnit\Framework\TestCase;

class CumulatorXSTest extends TestCase {

    public $mp;
    protected $stack;

    public function setUp() {
        $mp = Factory::properties();
        $this->mp = $mp;
        $this->stack = Factory::cumulator($mp, 2, Factory::XS);
    }

    public function testAddValues() {
        $arr = $this->additionProvider();
        foreach ($arr as $parm) {
            list ($val, $sum) = $parm;
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

    /**
     * @expectedException PHPUnit\Framework\Error\Error
     */
    public function testAddString() {
        $this->stack->add('abc');
    }

    public function testCumulate() {
        $amount = 10;
        $this->stack->add($amount);
        $this->assertSame($amount, $this->stack->sum());
        $this->assertSame($amount, $this->stack->sum(2));
        $this->mp->level = 2;
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
        $this->assertSame(null, $this->stack->nn());
        $this->assertSame(null, $this->stack->nz());
    }

    public function testMinMax() {
        $this->assertSame(null, $this->stack->min());
        $this->assertSame(null, $this->stack->max());
    }

    public function additionProvider() {
        return [
            [2, 2],
            [10, 12],
            [4, 16],
        ];
    }

}
