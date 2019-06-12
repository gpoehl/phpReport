<?php

declare(strict_types=1);

/**
 * Unit test of Factory class
 */
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Cumulator;
use gpoehl\phpReport\CumulatorXL;
use gpoehl\phpReport\CumulatorXS;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Sheet;
use gpoehl\phpReport\FixedSheet;
use gpoehl\phpReport\MajorProperties;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase {

    public function testGetCumulatorClass() {
        $mp = Factory::properties();
        $this->assertInstanceOf(MajorProperties::class, $mp);
        $this->assertInstanceOf(Cumulator::class, Factory::cumulator($mp, 2, Report::REGULAR));
        $this->assertInstanceOf(CumulatorXS::class, Factory::cumulator($mp, 2, Report::XS));
        $this->assertInstanceOf(CumulatorXL::class, Factory::cumulator($mp, 2, Report::XL));
        $this->assertInstanceOf(Collector::class, Factory::collector());
        $test = Factory::collector();
        $this->assertInstanceOf(Collector::class, $test);
    }

    public function testInstantiateSheet() {
        $mp = Factory::properties();
        $this->assertInstanceOf(Sheet::class, Factory::sheet($mp, 2, Report::REGULAR));
    }

    public function testInstantiateFixedSheet() {
        $mp = Factory::properties();
        $this->assertInstanceOf(FixedSheet::class, Factory::sheet($mp, 2, Report::REGULAR, 0, 5));
    }

}
