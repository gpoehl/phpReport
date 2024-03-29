<?php

declare(strict_types=1);

/**
 * Unit test of Stringoutput class
 */
use gpoehl\phpReport\Output\StringOutput;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StringOutputTest extends TestCase {

    private StringOutput $mock;

    public function setup(): void {
        $this->mock = new StringOutput();
    }

    public function testWrite(): void {
        $out = $this->mock;
        $out->write('a', 1, 9);
        $out->write('b', 1);
        $out->write('c');
        $this->assertEquals('abc', $out->get());
    }

    public function testPrepend(): void {
        $out = $this->mock;
        $out->prepend('a', 1, 9);
        $out->prepend('b', 1);
        $out->prepend('c');
        $this->assertEquals('cba', $out->get());
    }

    public function testGet(): void {
        $out = $this->mock;
        $out->write('abc');
        $this->assertEquals('abc', $out->get());
        $this->assertEquals('abc', $out->get(1));
        $this->assertEquals('abc', $out->get(1, 2));
    }

    public function testPop(): void {
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

    public function testDelete(): void {
        $out = $this->mock;
        $out->write('abc');
        $out->delete();
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $out->delete(1);
        $this->assertEquals('', $out->get());
        $out->write('abc');
        $out->delete(1, 2);
        $this->assertEquals('', $out->get());
    }

    #[DataProvider('separatorProvider')]
    public function testSeparator($expect, $glue): void {
        $out = $this->mock;
        $out->setSeparator($glue);
        $out->write('v1');
        $out->write('v2');
        $this->assertEquals($expect, $out->get());
    }

    public static function separatorProvider(): array {
        return [
            'blank' => ['v1 v2', ' '],
            'dash with blanks' => ['v1 - v2', ' - '],
            'br' => ['v1<br>v2', '<br>'],
            'none' => ['v1v2', ''],
            'nl' => ["v1\nv2", "\n"],
            'nl tab' => ["v1\n\tv2", "\n\t"],
            'special chr' => ["v1äöüv2", "äöü"],
        ];
    }
}
