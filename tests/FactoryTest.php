<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FactoryTest
 *
 * @author Günter
 */
use gpoehl\backbone\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase {

    public function testGetCumulatorClass() {
        $mp = Factory::properties();
        $this->assertInstanceOf(\gpoehl\backbone\MajorProperties::class,$mp);
         $this->assertInstanceOf(\gpoehl\backbone\Cumulator::class, Factory::cumulator($mp, 2, Factory::REGULAR));
         $this->assertInstanceOf(\gpoehl\backbone\CumulatorXS::class, Factory::cumulator($mp, 2, Factory::XS));
         $this->assertInstanceOf(\gpoehl\backbone\CumulatorXL::class, Factory::cumulator($mp, 2, Factory::XL));
         $this->assertInstanceOf(\gpoehl\backbone\Collector::class, Factory::collector());
         $test = Factory::collector();
         $this->assertInstanceOf(\gpoehl\backbone\Collector::class, $test);
    }


}
