<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Group;
use PHPUnit\Framework\TestCase;

class DimensionTest extends TestCase
{

    public $stack;
    public $total;

    public function setUp(): void {
        $this->total = new Collector();
        $this->stack = new Dimension(1, 4, 'DefaultTarget');
    }

    /**
     * @dataProvider rowProvider
     */
    public function testGetGroupValues($row) {
        $group1 = new Group('A', 5, 1, 'Attr1', []);
        $this->stack->groups[] = $group1;
        $group2 = new Group('B', 6, 1, 'Attr2', []);
        $this->stack->groups[] = $group2;
        $this->assertSame([5 => 'a', 'b'], $this->stack->getGroupValues($row, []));
    }

    /**
     * @dataProvider rowProvider
     */
    public function testGetJoinedData($row) {
        $this->stack->setJoinSource('Attr5', []);
        $this->stack->getGroupValues($row);   // Required to setGetters()

        $this->stack->activateValues($row);
        $this->assertSame([['x'], ['y'], ['z']], $this->stack->getJoinedData());
    }

    public function rowProvider() {
        $row = ['Attr1' => 'a', 'Attr2' => 'b', 'Attr3' => 3, 'Attr4' => 4, 'Attr5' => [['x'], ['y'], ['z']]];
        return [
            [$row],
            [(object) $row]
        ];
    }

}
