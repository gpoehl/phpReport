<?php

declare(strict_types=1);

/**
 * Description of GroupTest
 *
 * @author Guenter
 */
use gpoehl\backbone\Factory;
use gpoehl\backbone\CumulatorXS;
use PHPUnit\Framework\TestCase;

class BucketFixedTest extends TestCase {

    public $b;

    public function setUp() {
        $mp = Factory::properties();
        $cumulator = Factory::cumulator($mp, 3, Factory::XS);
        $this->b = Factory::sheet($cumulator, 1, 6);
    }

    public function testInstantiate() {
        $this->assertSame(6, count($this->b->getItems()));
        $this->assertInstanceOf(CumulatorXS::class, $this->b->getItem(1));
    }

     public function testAddItemViaArrayAccessThrowsException() {
        $this->expectException(Exception::class);
        $this->b[10]= 55;
    }
    public function testAddItemThrowsException() {
        $this->expectException(Exception::class);
        $this->b->add('NewItem1', 1);
    }
    public function testAskForMissingKey() {
        $this->assertSame(0, $this->b->rsum('NotExistingItem'));
        $this->assertFalse(isset($this->b->NotExistingItem), "rsum does not add not exitsting items");
    }

}
