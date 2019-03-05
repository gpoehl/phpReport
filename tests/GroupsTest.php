<?php

declare(strict_types = 1);

/**
 * Description of GroupTest
 *
 * @author Guenter
 */
use PHPUnit\Framework\TestCase;
use gpoehl\backbone\Groups;

class GroupsTest extends TestCase {

    public $methodNames = [
        'init' => 'init',
        'totalHeader' => 'header',
        'groupHeader' => 'header_%', // % will be replaced by group name or level
        'detail' => 'detail',
        'groupFooter' => 'footer_%', // % will be replaced by group name or level
        'totalFooter' => 'footer',
        'finalize' => 'finalize',
        'fetchValues_0' => 'fetchValues', // Dimension 0. 
        'fetchValues_n' => 'fetchValues_%', // Dimension > 0. % will be replaced by dimension
    ];
    public $groups;

//    /**
//     * @dataProvider providerTestInstantiateGroups
//     */
//    public function testInstantiateGroupsforMethodByName(): void {
//        $groups = new Groups(true, $this->methodNames);
//        $this->assertSame(0, count($groups->groupNames()));
//        $this->groups = $groups;
//    }
    /**
     * dataProvider providerTestInstantiateGroups
     */
    public function testInstantiateClassAndAddGroupsByName(): void {
        $groups = new Groups('header_%', 'footer_%', true);
        $this->assertSame(0, count($groups->groups));
        $this->assertSame(0, count($groups->groupsByName));
        $this->assertFalse($groups->getGroupLevel('no group created'));

        $groups->newGroup('groupA', 1);
        $this->assertSame(1, count($groups->groups));
        $this->assertSame(1, count($groups->groupsByName));
        $this->assertSame('groupA', sprintf($groups->groups[1]));
        $this->assertSame('groupA', $groups->groups[1]->groupName);
        $this->assertSame('header_groupA', $groups->groups[1]->headerCallable[1]);
        $this->assertSame('footer_groupA', $groups->groups[1]->footerCallable[1]);
        $this->assertSame('header_groupA', $groups->groupsByName['groupA']->headerCallable[1]);
        $this->assertSame('footer_groupA', $groups->groupsByName['groupA']->footerCallable[1]);
        $this->assertSame(1, $groups->getGroupLevel('groupA'));

        $groups->newGroup('groupB', 1);
        $this->assertSame(2, count($groups->groups));
        $this->assertSame(2, count($groups->groupsByName));
        $this->assertSame('groupB', sprintf($groups->groups[2]));
        $this->assertSame('groupB', $groups->groups[2]->groupName);
        $this->assertSame('header_groupB', $groups->groups[2]->headerCallable[1]);
        $this->assertSame('footer_groupB', $groups->groups[2]->footerCallable[1]);
        $this->assertSame('header_groupB', $groups->groupsByName['groupB']->headerCallable[1]);
        $this->assertSame('footer_groupB', $groups->groupsByName['groupB']->footerCallable[1]);

        $this->assertSame(2, $groups->getGroupLevel('groupB'));
        $this->assertFalse($groups->getGroupLevel('nonExistingGroup'));
    }

    public function testInstantiateClassAndAddGroupsByLevel() {
         $groups = new Groups('header_%', 'footer_%', false);
        $groups->newGroup('groupA', 1);
        $this->assertSame(1, count($groups->groups));
        $this->assertSame(1, count($groups->groupsByName));
        $this->assertSame('groupA', sprintf($groups->groups[1]));
        $this->assertSame('header_1', $groups->groups[1]->headerCallable[1]);
        $this->assertSame('footer_1', $groups->groups[1]->footerCallable[1]);
        $this->assertSame('header_1', $groups->groupsByName['groupA']->headerCallable[1]);
        $this->assertSame('footer_1', $groups->groupsByName['groupA']->footerCallable[1]);
        $groups->newGroup('groupB', 1);
        $this->assertSame('header_2', $groups->groups[2]->headerCallable[1]);
        $this->assertSame('footer_2', $groups->groups[2]->footerCallable[1]);
        $this->assertSame('header_2', $groups->groupsByName['groupB']->headerCallable[1]);
        $this->assertSame('footer_2', $groups->groupsByName['groupB']->footerCallable[1]);
    }

}
