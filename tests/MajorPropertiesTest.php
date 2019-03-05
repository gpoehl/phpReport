<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FactoryTest
 *
 * @author Günter
 */

use gpoehl\backbone\MajorProperties;
use PHPUnit\Framework\TestCase;

class MajorPropertiesTest extends TestCase {

    public $stub;

    public function setUp() {
        
        $this->stub = new MajorProperties();
        $this->assertInstanceOf(MajorProperties::class, $this->stub);
    }

    public function testLevel() {
        $this->stub->level = 4;
        $this->assertSame(4, $this->stub->level);
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

}
