<?php

declare(strict_types=1);

/**
 * Unit test of Sheet class
 */
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{

    public $b;

    public function setUp(): void {
        $rep = $this->createStub(Report::class);
        $rep->method('getLevel')
                ->will($this->returnCallback(fn($val) => $val ??= 0));
        $this->b = Factory::sheet($rep, 3, Report::XL);
    }

    public function testSum() {
        $this->b->add(['NewItem1' => 1]);
        $this->b->add(['NewItem3' => 3]);
        $this->assertSame(1, $this->b['NewItem1']->sum(), "Array Access");
        $this->assertSame(3, $this->b->NewItem3->sum(), "OO Type Access");

        $this->assertSame(4, $this->b->sum());
        $this->assertSame(['NewItem1' => 1, 'NewItem3' => 3], $this->b->sum(null, true), "ForEach");
    }

    public function testCounter() {
        $this->b->add(['NewItem1' => 1, 'NewItem3' => 3]);
        $this->b->add(['NewItem3' => 3]);
        $this->b->add(['NewItem3' => 0]);
        $this->b->add(['NewItem3' => null]);
        $this->assertSame(1, $this->b->NewItem1->nn());
        $this->assertSame(1, $this->b->NewItem1->nz());
        $this->assertSame(3, $this->b->NewItem3->nn());
        $this->assertSame(2, $this->b->NewItem3->nz());

        $this->assertSame(4, $this->b->nn());
        $this->assertSame(3, $this->b->nz());
        $this->assertSame(['NewItem1' => 1, 'NewItem3' => 3], $this->b->nn(null, true), "ForEach nn");
        $this->assertSame(['NewItem1' => 1, 'NewItem3' => 2], $this->b->nz(null, true), "ForEach nz");
    }

}
