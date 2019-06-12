<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Group;

class GroupTest extends TestCase {

    public function testInstantiateGroup() {
        $group = new Group('groupA', 2, 1);
        $this->assertSame('groupA', $group->groupName);
        $this->assertSame(2, $group->level);
        $this->assertSame(1, $group->dimID);
    }

    public function testgetGroupNameReplacement()  {
        $group = new Group('groupA', 2, 1);
        $this->assertSame(2, $group->getGroupNameReplacement(false));
        $this->assertSame('GroupA', $group->getGroupNameReplacement('ucfirst'));
        $this->assertSame('groupA', $group->getGroupNameReplacement(true));
    }

}
