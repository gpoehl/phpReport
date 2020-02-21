<?php

declare(strict_types=1);

/**
 * Unit test of Report class.
 * For tests with multiple dimensions see ReportMultipleDimensionTest file
 */
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ReportInfoMethodsTest extends TestCase {

    public $rep;

    public function setUp(): void {
        $this->rep = (new Report($this));
        $this->rep->group('a', 'ga')
                ->group('b', 'gb')
                ->run([['ga' => 1, 'gb' => 2]]);
    }

    public function testIsFirst() {
        $this->assertSame(True, $this->rep->isFirst());
    }

    public function testIsLast() {
        $this->assertSame(True, $this->rep->isLast());
    }

    public function testGetDimId() {
        $this->assertSame(0, $this->rep->getDimID());
    }

    public function testLevel() {
        $this->assertSame(0, $this->rep->getLevel());
    }

    public function testGetGroupValues() {
        $this->assertSame(0, $this->rep->getGroupValues());
    }

    public function testGetGroupValue() {
        $this->assertSame(0, $this->rep->getGroupValue());
    }

    public function testGetGroupNames() {
        $this->assertSame(0, $this->rep->getGroupNames());
    }

    public function testGetGroupName() {
        $this->assertSame(0, $this->rep->getGroupName());
    }

    public function gaHeader($value, $row) {
        $this->testIsFirst();
        $this->testGetCurrentDimId();
        $this->testLevel();
        $this->testGetGroupValues();
        $this->testGetGroupValue();
        $this->testGetGroupNames();
        $this->testGetGroupName();
    }

    public function gbHeader($value, $row) {
        $this->testIsFirst();
    }

    public function gaFooter($value, $row) {
        $this->testIsLast();
    }

    public function gbFooter($value, $row) {
        $this->testIsLast();
    }

}
