<?php

declare(strict_types=1);

/**
 * Unit test of Groups class
 */

use gpoehl\phpReport\Group;
use gpoehl\phpReport\Groups;
use PHPUnit\Framework\TestCase;

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

        $groups->addGroup(new Group('groupA', 1, 0, 1, []));
        $this->assertSame(1, count($groups->items));
        $this->assertSame('groupA', $groups->items[1]->name);
        $this->assertSame(1, $groups->groupLevel['groupA']);

        $groups->addGroup(new Group('groupB', 2, 0, 1, []));
        $this->assertSame(2, count($groups->items));
        $this->assertSame('groupB', $groups->items[2]->name);
        $this->assertSame(2, $groups->groupLevel['groupB']);
    }

    public function testAddGroupNameTwiceWillFail() {
        $groups = $this->stack;
        $groups->addGroup(new Group('groupA', 1, 0, 1, []));
        $this->expectException(InvalidArgumentException::class);
        $groups->addGroup(new Group('groupA', 2, 0, 1, []));
    }

    public function testFirstGroupLevelIsNotOne() {
        $groups = $this->stack;
        $this->expectException(InvalidArgumentException::class);
        $groups->addGroup(new Group('groupA', 2, 0, 1, []));
    }

    /**
     * @dataProvider groupProvider
     */
    public function testAddGroupInvalidLevel($group) {
        $groups = $this->stack;
        $groups->addGroup(new Group('group1', 1, 0, 1, []));
        $groups->addGroup(new Group('group2', 2, 0, 1, []));
        $this->expectException(InvalidArgumentException::class);
        $groups->addGroup($group);
    }

    public function groupProvider() {
        return [
            ['Level is less' => new Group('group3', 1, 1, 1,[])],
            ['Level is too high' => new Group('group5', 5, 1, 1,[])],
        ];
    }

}
