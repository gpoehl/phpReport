<?php

declare(strict_types=1);

/**
 * Unit test of Basic output class
 */
use PHPUnit\Framework\TestCase;

class StringOutputTest extends TestCase
{

    public function setup(): void {
        $this->mock = new \gpoehl\phpReport\output\StringOutput();
    }

    public function testWrite() {
        $out = $this->mock;
        $out->write('a', 1, 'i');
        $out->write('b', 1);
        $out->write('c');
        $this->assertEquals('abc', $out->get());
    }

    public function testPrepend() {
        $out = $this->mock;
        $out->prepend('a', 1, 'i');
        $out->prepend('b', 1, 'gh');
        $out->prepend('c', 1, 'tf');
        $this->assertEquals('cba', $out->get());
    }

    public function testGet() {
        $out = $this->mock;
        $out->write('abc');
        $this->assertEquals('abc', $out->get());
        $this->assertEquals('abc', $out->get(1));
        $this->assertEquals('abc', $out->get(1, 'gf'));
    }

    public function testPop() {
        $out = $this->mock;
        $out->write('abc');
        $this->assertEquals('abc', $out->pop());
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $this->assertEquals('abc', $out->pop(1));
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $this->assertEquals('abc', $out->pop(1, 'init'));
        $this->assertEquals('', $out->get());
    }

    public function testDelete() {
        $out = $this->mock;
        $out->write('abc');
        $out->delete();
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $out->delete(1);
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $out->delete(1, 'init');
        $this->assertEquals('', $out->get());
    }

    /**
     * @dataProvider separatorProvider
     */
    public function testSeparator($expect, $glue) {
        $out = $this->mock;
        $out->setSeparator($glue);
        $out->write('v1');
        $out->write('v2');
        $this->assertEquals($expect, $out->get());
    }

    public function separatorProvider() {
        return [
          'blank'=>  ['v1 v2', ' '],
          'dash with blanks' =>  ['v1 - v2', ' - '],
          'br' =>  ['v1<br>v2', '<br>'],
          'none' =>  ['v1v2', ''],
          'nl' =>  ["v1\nv2", "\n"],
          'nl tab' =>  ["v1\n\tv2", "\n\t"],
          'special chr' =>  ["v1äöüv2", "äöü"],
        ];
    }

}
