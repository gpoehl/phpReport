<?php

declare(strict_types=1);

/**
 * Unit test of Factory class
 */
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Calculator;
use gpoehl\phpReport\CalculatorXL;
use gpoehl\phpReport\CalculatorXS;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Sheet;
use gpoehl\phpReport\FixedSheet;
use gpoehl\phpReport\MajorProperties;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{

    private $rep;

    public function setUp(): void {
        $this->rep = $this->createStub(Report::class);
        $this->rep->method('getLevel')
                ->will($this->returnCallback(fn($val) => $val ??= 0));
    }

    public function testGetCalculatorClass() {
        $this->assertInstanceOf(Calculator::class, Factory::calculator($this->rep, 2, Report::REGULAR));
        $this->assertInstanceOf(CalculatorXS::class, Factory::calculator($this->rep, 2, Report::XS));
        $this->assertInstanceOf(CalculatorXL::class, Factory::calculator($this->rep, 2, Report::XL));
        $this->assertInstanceOf(Collector::class, Factory::collector());
        $test = Factory::collector();
        $this->assertInstanceOf(Collector::class, $test);
    }

    public function testInstantiateSheet() {
        $this->assertInstanceOf(Sheet::class, Factory::sheet($this->rep, 2, Report::REGULAR));
    }

    public function testInstantiateFixedSheet() {
        $this->assertInstanceOf(FixedSheet::class, Factory::sheet($this->rep, 2, Report::REGULAR, 0, 5));
    }

}
