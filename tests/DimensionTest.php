<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Group;
use PHPUnit\Framework\TestCase;

class DimensionTest extends TestCase {

    public $stack;
    public $total;
    public $mp;

    public function setUp(): void {
        $this->mp = Factory::properties();
        $this->total = Factory::collector();
        $this->stack = new Dimension(1, 4, 'DefaultTarget', $this->total);
    }

    /**
     * @dataProvider rowProvider
     */
    public function testGetGroupValues($row) {
        $group1 = new Group('A', 5, 1, 'Attr1');
        $this->stack->groups[] = $group1;
        $group2 = new Group('B', 6, 1, 'Attr2');
        $this->stack->groups[] = $group2;
        $this->assertSame([5 => 'a', 'b'], $this->stack->getGroupValues($row, null));
    }
    
    /**
     * @dataProvider rowProvider
     */
    public function testGetJoinedData($row) {
        $this->stack->setJoinSource('Attr5');
        $this->stack->getGroupValues($row);   // Required to setGetters()

        $this->stack->activateValues($row);
        $this->assertSame([['x'],['y'],['z'] ], $this->stack->getJoinedData());
    }

    /**
     * @dataProvider rowProvider
     */
    public function testAddValues($row) {
        $this->total->addItem(Factory::calculator($this->mp, 1, 1), 'A');
        $this->stack->addCalcSource('A', 'Attr3');

        $this->total->addItem(Factory::calculator($this->mp, 1, 1), 'B');
        $this->stack->addCalcSource('B', 'Attr4');

        $this->total->addItem(Factory::sheet($this->mp, 1, 1), 'C');
        $this->stack->addSheetSource('C', 'Attr3', 'Attr4');

        $this->stack->getGroupValues($row);   // Required to setGetters()
        $this->mp->level = 1;

        $this->stack->activateValues($row);
        $this->stack->addValues();

        $this->assertSame(['A' => 3, 'B' => 4, 'C' => 4], $this->total->sum(0, true));
        $this->assertSame([3 => 4], $this->total->C->sum(0, true));
    }

    public function rowProvider() {
        $row = ['Attr1' => 'a', 'Attr2' => 'b', 'Attr3' => 3, 'Attr4' => 4, 'Attr5' => [['x'],['y'],['z'] ]];
        return [
            [$row],
            [(object) $row]
        ];
    }

//    
}
