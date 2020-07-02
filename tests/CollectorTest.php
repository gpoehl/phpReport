<?php

declare(strict_types=1);

/**
 * Unit test of Collector class
 */
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;

class CollectorTest extends TestCase {

    public $mp;
    protected $stack;
    protected $rc1, $rc2, $rc3, $sheet;

    public function setUp(): void {
        $mp = Factory::properties();
        $this->mp = $mp;

        $this->sheet = Factory::sheet($mp, 3, Report::XL);

        $this->c1 = Factory::calculator($mp, 2, Report::XS);
        $this->c2 = Factory::calculator($mp, 4, Report::XS);
        $this->c3 = Factory::calculator($mp, 6, Report::REGULAR);
        $this->c4 = Factory::calculator($mp, 6, Report::XL);

        $this->stack = Factory::collector();
        $this->stack->addItem($this->c1, 1);    // start array index at 1
        $this->stack->addItem($this->c2);
        $this->stack->addItem($this->c3);
        $this->stack->addItem($this->c4);
        $this->stack->addItem($this->sheet);
    }

    public function testArrayAccess() {
        $this->stack[2]->add(4);
        $this->assertSame(4, $this->stack[2]->sum());
        $this->stack[] = $this->c2;
        $this->assertSame(4, $this->stack[6]->sum());
    }

    public function testMagicAccess() {
        $this->stack->{2}->add(4);
        $this->assertSame(4, $this->stack->{2}->sum());
        $this->stack->{5} = $this->c2;
        $this->assertSame(4, $this->stack->{5}->sum());
    }

    public function testAccessWithInvalidKeyProducesNotice() {
        $this->expectNotice();
        $a = $this->stack->getItem('missingKey');
    }

    public function testArrayAccessWithInvalidKeyProducesNotice() {
        $this->expectNotice();
        $a = $this->stack[99];
    }

    public function testMagicAccessWithInvalidKeyProducesNotice() {
        $this->expectNotice();
        $a = $this->stack->missingKey;
    }

    public function testIncrementOnMultipleXSCumulatorsHavingDifferentMaxLevels() {
        $this->c1->inc();
        $this->c1->inc();
        $this->assertSame(2, $this->stack->sum());
        // rc3 increments on level 6
        $this->c3->add(1);
        $this->assertSame(3, $this->stack->sum());
        $this->assertSame(1, $this->stack->sum(6));
        $this->mp->level = 6;
        $this->stack->cumulateToNextLevel();
        $this->assertSame(1, $this->stack->sum(5));
        $this->assertSame(1, $this->stack->sum(4));
        $this->assertSame(3, $this->stack->sum(2));
        $this->assertSame(3, $this->stack->sum(0));
    }

    public function testFormularsOnColletorsHavingDifferentTypOfCumulators() {
        $this->c1->inc();
        $this->c1->inc();
        $this->c3->add(2);
        $this->c3->add(0);
        $this->c3->add(null);
        $this->c4->add(5);
        $this->c4->add(3);
        $this->assertSame(12, $this->stack->sum(0), "Sum of all items at level 0");
        $this->assertSame([1 => 2, 0, 2, 8, 0], $this->stack->sum(0, true), "Sum of all items at level 0 as array");
        $this->assertSame(12, $this->stack->sum(2), "Sum of all items at level 2");
        $this->assertSame([1 => 2, 0, 2, 8, 0], $this->stack->sum(2, true), "Level 2 as array");
        $this->assertSame(10, $this->stack->sum(4), "C1 is does not exitst on level 4");
        $this->assertSame([1 => 0, 0, 2, 8, 0], $this->stack->sum(4, true), "C1 is part of result. All items in stack are in result array");
    }

    public function testAdd() {
        $this->stack->add([1 => 4, 6, 8, 5 => [3 => 1, 6 => 2, 9 => 3]]);
        $this->assertSame(4, $this->stack->{1}->sum(0), "Sum of item c1");
        $this->assertSame(6, $this->stack->{2}->sum(0), "Sum of item c2");
        $this->assertSame(8, $this->stack->{3}->sum(0), "Sum of item c3");
        $this->assertSame(0, $this->stack->{4}->sum(0), "Sum of item c4");
        $this->assertSame(6, $this->stack->{5}->sum(0), "Sum of sheet");
        $this->assertSame(24, $this->stack->sum(), "Sum of c1 to c4 and sheet");
    }

    public function testNnFailure() {
        $this->expectException(Error::class);
        $this->stack->nn();
    }

    public function testNzFailure() {
        $this->expectException(Error::class);
        $this->stack->nz();
    }

    public function testMinFailure() {
        $this->expectException(Error::class);
        $this->stack->min();
    }

    public function testMaxFailure() {
        $this->expectException(Error::class);
        $this->stack->max();
    }

  
    public function testIterativ() {
        $multi = Factory::collector();
        $this->stack->addItem($multi, 'multi');
        $s1 = Factory::calculator($this->mp, 2, Report::XS);
        $s2 = Factory::calculator($this->mp, 2, Report::XS);
        $multi->addItem($s1);
        $multi->addItem($s2);
        $s1->add(5);
        $s2->add(7);
        $this->c1->inc();
        $this->c1->inc();
        $this->c3->add(1);
        $this->assertSame(12, $multi->sum());
        $this->assertSame(15, $this->stack->sum(), "Sum incl sub collector");
        $this->assertSame([5, 7], $multi->range([0, 1])->sum(null, true), "range from collector multi key 1 to 2 as array");
        $this->assertSame([5, 7], $multi->between([0, 1])->sum(null, true), "between from collector multi key between 0 and 1 as array");
        $this->assertSame([3=>1, 0, 0, 'multi' => 12], $this->stack->between([3,5], 'multi')->sum(null, true), "between from stack collector key between 3 and 6 plus 'multi' as array");
    }

    /**
     * @dataProvider rangeParamsProvider
     */
    public function testRange($expected, ... $params) {
        $range = $this->stack->range(... $params);
        $this->assertSame(array_keys($range->items), $expected);
    }

    public function rangeParamsProvider() {
        return [
            'Range' => [[2, 3, 4], [2, 4]],
            'Single items' => [[2, 4], 2, 4],
            'Ranges and singles ' => [[1, 2, 3, 4], [1, 3], [3, 4], 2],
            'Start at Zero' => [[1, 2], [null, 2]],
            'No length' => [[3, 4, 5], [3, null]],
            'Single items missing' => [[2], 2, 7],
        ];
    }

    /**
     * @dataProvider betweenParamsProvider
     */
    public function testBetween($expected, ... $params) {
        $range = $this->stack->range(... $params);
        $this->assertSame(array_keys($range->items), $expected);
    }

    public function betweenParamsProvider() {
        return [
            'Range' => [[2, 3, 4], [2, 4]],
            'Single items' => [[2, 4], 2, 4],
            'Ranges and singles ' => [[1, 2, 3, 4], [1, 3], [3, 4], 2],
            'No fromKey' => [[1, 2], [null, 2]],
            'No toKey' => [[3, 4, 5], [3, null]],
            'Single items missing' => [[2], 2, 7],
        ];
    }

}
