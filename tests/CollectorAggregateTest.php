<?php

declare(strict_types=1);

/**
 * Unit test of Collector class.
 * Test the aggregate methods
 */
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Calculator\CalculatorXL;
use gpoehl\phpReport\Calculator\CalculatorBcmXL;
use PHPUnit\Framework\TestCase;

class CollectorAggregateTest extends TestCase
{

    public $stack;
    public $calcBcm;

    public function setUp(): void {
        $this->stack = new Collector();
        $calc = new CalculatorXL();
        $calc->initialize(fn($val) => $val ??= 0, 0);

        $this->stack->addItem($calc, 'a');
        $this->stack->addItem(clone $calc, 'b');
        $this->stack->add(['a' => 3, 'b' => 3]);
        $this->stack->add(['a' => 5, 'b' => 9]);
        $this->stack->b->add(0);
        $this->stack->b->add(null);

        $this->calcBcm = new CalculatorBcmXL(4);
        $this->calcBcm->initialize(fn($val) => $val ??= 0, 0);
        $this->calcBcm->add(1);
        $this->calcBcm->add(20 / 3);
    }

    /**
     * @dataProvider scaleProvider
     */
    public function testScaleSum($calculator, $expected): void {
        $col = new Collector();
        $calculator->initialize(fn($val) => $val ??= 0, 0);
        $this->assertSame(0, $col->sum());
        $col->setScale(3);
        $this->assertSame('0.000', $col->sum());
        $col->addItem($calculator);
        $this->assertSame('0.000', $col->sum());
        $calculator->add(10 / 3);
        $this->assertSame($expected, $col->sum());
    }

    /**
     * @dataProvider scaleProvider
     */
    public function testScaleCounter($calculator, $expected): void {
        $col = new Collector();
        $calculator->initialize(fn($val) => $val ??= 0, 0);
        $this->assertSame(0, $col->count());
        $col->setScale(3);
        $this->assertSame(0, $col->count());
        $col->addItem($calculator);
        $this->assertSame(0, $col->count());
        $calculator->add(10 / 3);
        $calculator->add(0);
        $calculator->add(null);
        $this->assertSame(3, $col->count());
        $this->assertSame(2, $col->countNN());
        $this->assertSame(1, $col->countNZ());
    }

    public function scaleProvider() {
        return [
            [new CalculatorXL, '3.333'],
            [new CalculatorBcmXL(1), '3.300'],
            [new CalculatorBcmXL(5), '3.333'],
        ];
    }

    public function testSetScale() {
        $this->assertSame(null, $this->stack->getScale());
        $this->stack->setScale(2);
        $this->assertSame(2, $this->stack->getScale());
    }

    public function testSum() {
        $this->stack->addItem($this->calcBcm, 'bcm');
        $this->assertSame(27.6666, round($this->stack->sum(), 4));
        $this->assertSame(['a' => 8, 'b' => 12, 'bcm' => '7.6666'],
                $this->stack->sum(depth: 1));
        $this->stack->setScale(2);
        $this->assertSame('27.66', $this->stack->sum());
        $this->assertSame(['a' => 8, 'b' => 12, 'bcm' => '7.6666'],
                $this->stack->sum(depth: 1));
    }

    public function testCounterAndAvg() {
        $this->assertSame(6, $this->stack->count());
        $this->assertSame(5, $this->stack->countNN());
        $this->assertSame(4, $this->stack->countNZ());
        $this->assertSame(['a' => 2, 'b' => 4], $this->stack->count(depth: 1));

        $this->assertSame(20, $this->stack->sum());

        $this->assertSame(3.33, round($this->stack->avg(), 2));
        $this->assertSame(4, $this->stack->avgNN());
        $this->assertSame(5, $this->stack->avgNZ());

        $this->assertSame(['a' => 4, 'b' => 3], $this->stack->avg(depth: 1));
        $this->stack->setScale(2);
        $this->assertSame(['a' => 4, 'b' => 6], $this->stack->avgNZ(depth: 1));
    }

    public function testMultiDimension() {
        // Add second dimension
        $this->stack->addItem(clone $this->stack, 'c');
        // Add third second dimension
        $this->stack->c->addItem(clone $this->stack->c, 'd');

        $this->assertSame(60, $this->stack->sum());
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => 40], $this->stack->sum(depth: 1));
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => ['a' => 8, 'b' => 12, 'd' => 20]], $this->stack->sum(depth: 2));
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => ['a' => 8, 'b' => 12, 'd' => ['a' => 8, 'b' => 12]]]
                , $this->stack->sum(depth: 99));

        $this->assertSame(18, $this->stack->count());
        $this->assertSame(15, $this->stack->countNN());
        $this->assertSame(12, $this->stack->countNZ());
        $this->assertSame(['a' => 2, 'b' => 4, 'c' => 12], $this->stack->count(depth: 1));

        $this->assertSame(3.33, round($this->stack->avg(), 2));
        $this->assertSame(4, $this->stack->avgNN());
        $this->assertSame(5, $this->stack->avgNZ());

        $this->assertSame(['a' => 4, 'b' => 6, 'c' => 5], $this->stack->avgNZ(depth: 1));
        $this->assertSame(['a' => 4, 'b' => 6, 'c' => ['a' => 4, 'b' => 6, 'd' => 5]], $this->stack->avgNZ(depth: 2));
        $this->assertSame(['a' => 4, 'b' => 6, 'c' => ['a' => 4, 'b' => 6, 'd' => ['a' => 4, 'b' => 6]]], $this->stack->avgNZ(depth: 99));

        $this->assertSame(0, $this->stack->min());
        $this->assertSame(9, $this->stack->max());
        $this->assertSame(['a' => 3, 'b' => 0, 'c' => 0], $this->stack->min(depth: 1));
        $this->assertSame(['a' => 5, 'b' => 9, 'c' => 9], $this->stack->max(depth: 1));
        $this->assertSame(['a' => 3, 'b' => 0, 'c' => ['a' => 3, 'b' => 0, 'd' => 0]], $this->stack->min(depth: 2));
        $this->assertSame(['a' => 5, 'b' => 9, 'c' => ['a' => 5, 'b' => 9, 'd' => 9]], $this->stack->max(depth: 2));
        $this->assertSame(['a' => 3, 'b' => 0, 'c' => ['a' => 3, 'b' => 0, 'd' => ['a' => 3, 'b' => 0]]], $this->stack->min(depth: 99));
        $this->assertSame(['a' => 5, 'b' => 9, 'c' => ['a' => 5, 'b' => 9, 'd' => ['a' => 5, 'b' => 9]]], $this->stack->max(depth: 99));
    }

    public function testMultiDimensionMixedBcm() {
        // Add second dimension
        $wrk = clone $this->stack;
        // set scale to to level collector
        $this->stack->setScale(3);
        $this->stack->addItem($wrk, 'c');
        // Add third second dimension
        $this->stack->c->addItem(clone $this->stack->c, 'd');

        $this->assertSame('60.000', $this->stack->sum());
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => 40], $this->stack->sum(depth: 1));
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => ['a' => 8, 'b' => 12, 'd' => 20]], $this->stack->sum(depth: 2));
        $this->assertSame(['a' => 8, 'b' => 12, 'c' => ['a' => 8, 'b' => 12, 'd' => ['a' => 8, 'b' => 12]]]
                , $this->stack->sum(depth: 99));

        $this->assertSame(18, $this->stack->count());
        $this->assertSame(15, $this->stack->countNN());
        $this->assertSame(12, $this->stack->countNZ());
        $this->assertSame(['a' => 2, 'b' => 4, 'c' => 12], $this->stack->count(depth: 1));

        $this->assertSame('3.333', $this->stack->avg());
        $this->assertSame('4.000', $this->stack->avgNN());
        $this->assertSame('5.000', $this->stack->avgNZ());

        $this->assertSame(['a' => 4, 'b' => 6, 'c' => 5], $this->stack->avgNZ(depth: 1));
        $this->assertSame(['a' => 4, 'b' => 6, 'c' => ['a' => 4, 'b' => 6, 'd' => 5]], $this->stack->avgNZ(depth: 2));
        $this->assertSame(['a' => 4, 'b' => 6, 'c' => ['a' => 4, 'b' => 6, 'd' => ['a' => 4, 'b' => 6]]], $this->stack->avgNZ(depth: 99));
    }

}
