<?php

declare(strict_types=1);

/**
 * Unit test of Groups class
 */
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Groups;

class GroupsTest extends TestCase {

    protected $stack;

    public function setUp(): void {
        $this->stack = $groups = new Groups('total');
    }

    public function testInstantiateClassAndAddGroups(): void {
        $groups = $this->stack;
        $this->assertSame(0, count($groups->items));
        $this->assertSame(1, count($groups->groupLevel));
        $this->assertSame(0, $groups->groupLevel['total']);

        $groups->newGroup('groupA', 1);
        $this->assertSame(1, count($groups->items));
        $this->assertSame('groupA', $groups->items[1]->groupName);
        $this->assertSame(1, $groups->groupLevel['groupA']);

        $groups->newGroup('groupB', 1);
        $this->assertSame(2, count($groups->items));
        $this->assertSame('groupB', $groups->items[2]->groupName);
        $this->assertSame(2, $groups->groupLevel['groupB']);
    }

    public function testAddGroupTwiceWillFail() {
        $groups = $this->stack;
        $groups->newGroup('groupA', 1);
        $this->expectException(InvalidArgumentException::class);
        $groups->newGroup('groupA', 1);
    }
   
}
