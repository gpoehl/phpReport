<?php

declare(strict_types=1);

/**
 * Unit test of Bandoutput class
 */
use gpoehl\phpReport\Output\BandOutput;
use PHPUnit\Framework\TestCase;

class BandOutputTest extends TestCase
{

    private BandOutput $out;
    
    public function setup(): void {
        $this->out = new BandOutput();
    }

    public function write($val, $level, $key) {
        $actionKey = gpoehl\phpReport\Actionkey::fromName($key);
        $this->out->write($val, $level, $this->out->actionKeyMapper[$actionKey]);
    }

    public function testWrite() {
        $this->write('a', 0, 'Start');
        $this->write('b', 0, 'TotalHeader');
        $this->write('c', 1, 'GroupHeader');
        $this->write('d', 2, 'GroupHeader');
        $this->write('e', 2, 'Detail');
        $this->write('f', 2, 'Detail');
        $this->write('g', 2, 'Detail');
        $this->write('h', 2, 'GroupFooter');
        $this->write('i', 1, 'GroupFooter');
        $this->write('j', 0, 'TotalFooter');
        $this->write('k', 0, 'Finish');
        $this->assertEquals(implode(range('a', 'k')), $this->out->get());
    }

    public function testWriteWithCumulate() {
        $this->write('a', 0, 'Start');
        $this->write('b', 0, 'TotalHeader');
        $this->write('c', 1, 'GroupHeader');
        $this->write('d', 2, 'GroupHeader');
        $this->write('e', 2, 'Detail');
        $this->write('f', 2, 'Detail');
        $this->write('g', 2, 'Detail');
        $this->write('h', 2, 'GroupFooter');
        $this->out->cumulateToNextLevel(2);
        $this->write('i', 2, 'GroupHeader');
        $this->write('j', 2, 'Detail');
        $this->write('k', 2, 'Detail');
        $this->write('l', 2, 'Detail');
        $this->write('m', 2, 'GroupFooter');
        $this->write('n', 1, 'GroupFooter');
        $this->write('o', 0, 'TotalFooter');
        $this->write('p', 0, 'Finish');
        $this->assertEquals(implode(range('a', 'p')), $this->out->get(0));
    }

    public function testGet() {
        $this->testWrite();
        $this->assertEquals(implode(range('a', 'k')), $this->out->get());
        $this->assertEquals(implode(range('a', 'k')), $this->out->get(0));
        $this->assertEquals(implode(range('c', 'i')), $this->out->get(1));
        $this->assertEquals(implode(range('d', 'h')), $this->out->get(2));
    }

    public function testDelete() {
        $this->testWrite();
        $out = $this->out;
        $out->delete(2);
        $this->assertEquals(implode(range('a', 'c')) . implode(range('i', 'k')), $out->get());
        $out->delete(1);
        $this->assertEquals('abjk', $out->get());
        $out->delete(0);
        $this->assertEquals('', $out->get());
    }

    public function testDeleteOtherLevel() {
        $this->testWrite();
        $this->out->delete(1);
        $this->assertEquals('abjk', $this->out->get());
    }

    public function testPop() {
        $this->testWrite();
        $out = $this->out;
        $this->assertEquals(implode(range('a', 'k')), $out->get());
        $this->assertEquals(implode(range('d', 'h')), $out->pop(2));
        $this->assertEquals(implode(range('a', 'c')) . implode(range('i', 'k')), $out->get());
        $this->assertEquals('ci', $out->pop(1));
        $this->assertEquals('abjk', $out->get());
        $this->assertEquals('abjk', $out->pop(0));
        $this->assertEquals('', $out->get());
    }

}
