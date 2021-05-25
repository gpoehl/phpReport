<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Group;

class GroupTest extends TestCase {

    public function testInstantiateGroup() {
        $group = new Group('groupA', 2, 1, 'testSource', []);
        $this->assertSame('groupA', $group->name);
        $this->assertSame(2, $group->level);
        $this->assertSame(1, $group->dimID);
        $this->assertSame('testSource', $group->valueSource);
    }
    
    public function testInvalidLevel() {
        $this->expectException(InvalidArgumentException::class);
        $group = new Group('groupA', 0, 1, 'testSource', []);
    }
     public function testInvalidDimID() {
        $this->expectException(InvalidArgumentException::class);
        $group = new Group('groupA', 1, -3, 'testSource', []);
    }

}
