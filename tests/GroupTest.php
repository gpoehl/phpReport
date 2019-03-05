<?php

declare(strict_types = 1);

/**
 * Description of GroupTest
 *
 * @author Guenter
 */
use PHPUnit\Framework\TestCase;
use gpoehl\backbone\Group;

class GroupTest extends TestCase {

  public function testInstantiateGroup() {
       
        $group = new Group('groupA', 2, 1, 'header_groupA', 'footer_groupA');
        $this->assertSame('groupA', $group->groupName);
        $this->assertSame(2, $group->level);
        $this->assertSame(1, $group->dim);
        $this->assertSame('header_groupA', $group->headerCallable[1]);
        $this->assertSame('footer_groupA', $group->footerCallable[1]);
    }
}
