<?php

declare(strict_types=1);

/**
 * Description of GroupTest
 *
 * @author Guenter
 */
use gpoehl\backbone\Factory;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase {

    public $b;

    public function setUp() {
        $mp = Factory::properties();
        $cumulator = Factory::cumulator($mp, 3, Factory::XL);
        $this->b = Factory::sheet($cumulator);
    }

    public function testAddItem() {
        $this->b->add('NewItem1', 1);
        $this->b->add('NewItem3', 3);
        $this->assertSame(1, $this->b['NewItem1']->sum(), "Array Access");
        $this->assertSame(3, $this->b->NewItem3->sum(), "OO Type Access");
         $this->assertSame(1, $this->b->rsum('NewItem1'), "ToKey defautls to fromKey");
         
        $this->assertSame(4, $this->b->sum());
        $this->assertSame(['NewItem1' => 1, 'NewItem3' => 3], $this->b->sum(null, true), "ForEach");
        $this->assertSame(['NewItem1' => 1, 'NewItem2' => Null, 'NewItem3' => 3], $this->b->rsum('NewItem1', 'NewItem3', null, true), "for loop returns not existing items.");
    }
    public function testAskForMissingKey() {
        $this->assertSame(0, $this->b->rsum('NotExistingItem'));
        $this->assertFalse(isset($this->b->NotExistingItem), "rsum does not add not exitsting items");
    }

}
