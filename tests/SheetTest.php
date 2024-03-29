<?php

declare(strict_types=1);

/**
 * Unit test of Sheet class
 */
use gpoehl\phpReport\Calculator\Calculator;
use gpoehl\phpReport\Sheet;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{

    public $stack;

    public function setUp(): void {
        $calc = new Calculator();
        $calc->initialize(fn($val) => $val ??= 0, 2);
        $this->stack = new Sheet($calc);
    }

    public function testSum() {
        $this->stack->add(['NewItem1' => 1]);
        $this->stack->add(['NewItem2' => 3]);
        $this->assertSame(1, $this->stack['NewItem1']->sum(), "Array Access");
        $this->assertSame(3, $this->stack->NewItem2->sum(), "OO Type Access");

        $this->assertSame(4, $this->stack->sum());
        $this->assertSame(['NewItem1' => 1, 'NewItem2' => 3], $this->stack->sum(null, 1), "ForEach");
    }

    public function testCounter() {
        $this->stack->add(['NewItem1' => 1, 'NewItem2' => 3]);
        $this->stack->add(['NewItem2' => 3]);
        $this->stack->add(['NewItem2' => 0]);
        $this->stack->add(['NewItem2' => null]);
        $this->assertSame(1, $this->stack->NewItem1->count());
        $this->assertSame(1, $this->stack->NewItem1->countNN());
        $this->assertSame(1, $this->stack->NewItem1->countNZ());
        $this->assertSame(4, $this->stack->NewItem2->count());
        $this->assertSame(3, $this->stack->NewItem2->countNN());
        $this->assertSame(2, $this->stack->NewItem2->countNZ());

        $this->assertSame(5, $this->stack->count());
        $this->assertSame(4, $this->stack->countNN());
        $this->assertSame(3, $this->stack->countNZ());
        $this->assertSame(['NewItem1' => 1, 'NewItem2' => 4], $this->stack->count(depth: 1), "Array count");
        $this->assertSame(['NewItem1' => 1, 'NewItem2' => 3], $this->stack->countNN(depth: 1), "Array countNN");
        $this->assertSame(['NewItem1' => 1, 'NewItem2' => 2], $this->stack->countNZ(depth: 1), "Array countNZ");
    }

}
