<?php

declare(strict_types=1);
/**
 * Unit test of MajorProperties class
 */
use gpoehl\phpReport\MajorProperties;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;

class MajorPropertiesTest extends TestCase {

    public $stub;

    public function setUp(): void {
        $mp = new MajorProperties();
        $mp->level = 3;
        $mp->maxLevel = $mp->level;
        $mp->groupLevel = ['total' => 0, 'g1' => 1, 'g2' => 2, 'g3' => 3];
        $this->stub = $mp;
        $this->assertInstanceOf(MajorProperties::class, $this->stub);
    }

    public function testLevel() {
        $this->assertSame(3, $this->stub->level);
    }

    public function testRC() {
        $this->assertFalse(isset($this->stub->rc[1]));
        $this->stub->rc[2] = 4;
        $this->assertSame(4, $this->stub->rc[2]);
    }

    public function testGC() {
        $this->assertFalse(isset($this->stub->gc[1]));
        $this->stub->gc[2] = 4;
        $this->assertSame(4, $this->stub->gc[2]);
    }

    public function testGetLevelForNull() {
        $this->assertSame(3, $this->stub->getLevel());
    }

    public function testGetLevelForNullAtDetail() {
        $this->stub->level = $this->stub->maxLevel + 1;
        $this->assertSame(3, $this->stub->getLevel());
    }

    public function testGetLevelForNumber() {
        $this->assertSame(2, $this->stub->getLevel(2));
        $this->assertSame(4, $this->stub->getLevel(4));
    }

    public function testGetLevelForGroupName() {
        $this->assertSame(0, $this->stub->getLevel('total'));
        $this->assertSame(2, $this->stub->getLevel('g2'));
    }

    public function testGetLevelForInvalidGroupName() {
        $this->expectNotice();
        $this->stub->getLevel('notExistingGroup');
    }
    
    public function testGetLevelNegativeValue() {
        $this->assertSame(1, $this->stub->getLevel(-2));
    }

}
