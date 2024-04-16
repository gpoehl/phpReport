<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Dimensions;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase {

    public $dims;
    public $dim0;
    public $dim1;

    public function setUp(): void {
        $this->dims = new Dimensions();
        $this->dim0 = new Dimension('dim0');
        $this->dim1 = new Dimension('dim1');
    }

    public function testConstruct(): void {
        $dims = new Dimensions([$this->dim0, $this->dim1]);
        $this->assertSame(2, $dims->count());
    }

    public function testConstructFails(): void {
        $this->expectException(TypeError::class);
        $this->dims->append([1, 2, 3], 'A');
    }

    public function testAppend(): void {
        $this->dims->append($this->dim0);
        $this->assertSame(1, $this->dims->count());
    }

    public function testAppendFails(): void {
        $this->expectException(TypeError::class);
        $this->dims->append([1, 2, 3]);
    }

    public function testAdd(): void {
        $this->dims->push($this->dim0);
        $this->dims->push($this->dim1);
        $this->dims->push(new Dimension('dim2'));
        $this->assertSame(3, $this->dims->count());
        $this->assertSame('dim2', $this->dims[2]->name);
        $this->assertSame(2, $this->dims[2]->id);
    }

    /**
     * Dimension should not be appended using array access.
     * Can't force validation of add() method.
     * @return void
     */
    public function testAppendByArrayAccess(): void {
        $this->dims[] = $this->dim0;
        $this->assertSame(1, $this->dims->count());
        $this->dims[] = $this->dim0;
        $this->assertSame(2, $this->dims->count());
    }

//
    public function testAddWithSameNameFails(): void {
        $this->dims->push($this->dim0);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Dimension name 'dim0' already exists.");
        $this->dims->push(new Dimension('dim0'));
    }
}
