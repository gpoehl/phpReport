<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Group;
use gpoehl\phpReport\Groups;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DimensionTest extends TestCase {

    public $stack;
    public $total;

    public function setUp(): void {
        $this->total = new Collector();
        $this->stack = new Dimension('0', 'DefaultTarget');
    }

    #[DataProvider('rowProvider')]
    public function testGetGroupValues($row): void {
        $groups = new Groups('total');
        $group1 = new Group('A', 1, 'Attr1', []);
        $groups->addGroup($group1);
        $this->stack->groups[] = $group1;
        $group2 = new Group('B', 1, 'Attr2', []);
        $groups->addGroup($group2);
        $this->stack->groups[] = $group2;
        $this->assertSame([1 => 'a', 'b'], $this->stack->getGroupValues($row, null));
    }

    #[DataProvider('rowProvider')]
    public function testGetJoinedData($row): void {
        $this->stack->setJoinSource('Attr5', []);
        $this->stack->getGroupValues($row, null);   // Required to setGetters()

        $this->stack->activateValues($row, null, []);
        $this->assertSame([['x'], ['y'], ['z']], $this->stack->getJoinedData());
    }

    public static function rowProvider(): array {
        $row = ['Attr1' => 'a', 'Attr2' => 'b', 'Attr3' => 3, 'Attr4' => 4, 'Attr5' => [['x'], ['y'], ['z']]];
        return [
            [$row],
            [(object) $row]
        ];
    }
}
