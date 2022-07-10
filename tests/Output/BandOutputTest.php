<?php

declare(strict_types=1);

/**
 * Unit test of Bandoutput class
 */
use gpoehl\phpReport\Output\BandOutput;
use PHPUnit\Framework\TestCase;

class BandOutputTest extends TestCase
{

    public function setup(): void {
        $this->mock = new BandOutput();
    }

    public function write($val, $level, $key) {
        $this->mock->write($val, $level, $this->mock->actionKeyMapper[$key]);
    }

    public function testWrite() {
        $this->write('a', 0, 'init');
        $this->write('b', 0, 'totalHeader');
        $this->write('c', 1, 'groupHeader');
        $this->write('d', 2, 'groupHeader');
        $this->write('e', 2, 'detail');
        $this->write('f', 2, 'detail');
        $this->write('g', 2, 'detail');
        $this->write('h', 2, 'groupFooter');
        $this->write('i', 1, 'groupFooter');
        $this->write('j', 0, 'totalFooter');
        $this->write('k', 0, 'close');
        $this->assertEquals(implode(range('a', 'k')), $this->mock->get());
    }

    public function testWriteWithCumulate() {
        $this->write('a', 0, 'init');
        $this->write('b', 0, 'totalHeader');
        $this->write('c', 1, 'groupHeader');
        $this->write('d', 2, 'groupHeader');
        $this->write('e', 2, 'detail');
        $this->write('f', 2, 'detail');
        $this->write('g', 2, 'detail');
        $this->write('h', 2, 'groupFooter');
        $this->mock->cumulateToNextLevel(2);
        $this->write('i', 2, 'groupHeader');
        $this->write('j', 2, 'detail');
        $this->write('k', 2, 'detail');
        $this->write('l', 2, 'detail');
        $this->write('m', 2, 'groupFooter');
        $this->write('n', 1, 'groupFooter');
        $this->write('o', 0, 'totalFooter');
        $this->write('p', 0, 'close');
        $this->assertEquals(implode(range('a', 'p')), $this->mock->get(0));
    }

    public function testGet() {
        $this->testWrite();
        $out = $this->mock;
        $this->assertEquals(implode(range('a', 'k')), $out->get());
        $this->assertEquals(implode(range('a', 'k')), $out->get(0));
        $this->assertEquals(implode(range('c', 'i')), $out->get(1));
        $this->assertEquals(implode(range('d', 'h')), $out->get(2));
    }

    public function testDelete() {
        $this->testWrite();
        $out = $this->mock;
        $out->delete(2);
        $this->assertEquals(implode(range('a', 'c')) . implode(range('i', 'k')), $out->get());
        $out->delete(1);
        $this->assertEquals('abjk', $out->get());
        $out->delete(0);
        $this->assertEquals('', $out->get());
    }

    public function testDeleteOtherLevel() {
        $this->testWrite();
        $out = $this->mock;
        $out->delete(1);
        $this->assertEquals('abjk', $out->get());
    }

    public function testPop() {
        $this->testWrite();
        $out = $this->mock;
        $this->assertEquals(implode(range('a', 'k')), $out->get());
        $this->assertEquals(implode(range('d', 'h')), $out->pop(2));
        $this->assertEquals(implode(range('a', 'c')) . implode(range('i', 'k')), $out->get());
        $this->assertEquals('ci', $out->pop(1));
        $this->assertEquals('abjk', $out->get());
        $this->assertEquals('abjk', $out->pop(0));
        $this->assertEquals('', $out->get());
    }

}
