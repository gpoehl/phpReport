<?php

declare(strict_types=1);

/**
 * Unit test of Report class.
 * For tests with multiple dimensions see ReportMultipleDimensionTest file
 */
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\output\AbstractOutput;
use gpoehl\phpReport\Report;
use gpoehl\phpReport\RuntimeOption;
use gpoehl\phpReport\PrototypeMini;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase {

    public function testBasics(): void {
        $rep = (new Report());
        $this->assertInstanceOf(Collector::class, $rep->rc);
        $this->assertInstanceOf(Collector::class, $rep->gc);
        $this->assertInstanceOf(Collector::class, $rep->total);
        $this->assertSame(Null, $rep->params);
        $this->assertInstanceOf(AbstractOutput::class, $rep->out);
    }

    #[DataProvider('paramsProvider')]
    public function testConstructorParams($data): void {
        $rep = (new Report(null, null, null, $data));
        $this->assertSame($data, $rep->params);
    }

    public static function paramsProvider(): array {
        return [
            'Empty param' => [null],
            'One param' => ['myParam'],
            'One param as assoc array' => [['p1' => 'param1', 'p2' => 'param2', 'p3' => 'param3']],
        ];
    }

    #[DataProvider('noDataProvider')]
    public function testNoData($data, $expected): void {
        $rep = new Report($this, [
            'prototype' => PrototypeMini::class,
            'actions' => ['NoData' => 'nodata']]);
        $rep->setRuntimeOption(RuntimeOption::Prototype);
        $rep->run($data);
        $this->assertSame('start, headerTotal, ' . $expected . 'footerTotal, finish, ', $rep->out->get());
    }

    public static function noDataProvider(): array {
        return [
            'Data set equals null' => [null, 'nodata, '],
            'Data set is an empty array' => [[], 'nodata, '],
        ];
    }

    // String is not iterable
    public function testStringAsData(): void {
        $this->expectException(Error::class);
        (new Report($this))->run('xx');
    }

    public function testNoGroupsDefined(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->run([['A']]);
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep);
    }

    public function testRunCompleteIsFalse(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->run([['A']], false)
                ->end();
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep);
    }

    public function testCallEndMethodWhenFinalizeIsTrueFails(): void {
        $this->expectException(Error::class);
        $rep = (new Report($this))
                ->run(['A'])               // run returns a string
                ->end();                   // method chaining works only on objects.
    }

    public function testChunkOfRowsWithParamCompleteIsFalseAndNext(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->run([['A'], ['B']], false);
        $rep->next(['C']);
        $rep->end();
        $this->assertSame('start, headerTotal, detail, detail, detail, footerTotal, finish, ', $rep->out->get());
    }

    public function testNextForOneRowNoGroups(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->run(null, false);
        $rep->next(['A']);
        $rep->end();
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep->out->get());
    }

    public function testGroupsWithOneDataRow(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->group('a', 'firstGroup')
                ->group('b', 'secondGroup')
                ->run([['firstGroup' => 'A', 'secondGroup' => 'X']]);
        $this->assertSame('start, headerTotal, beforeA, headerA, beforeB, headerB, ' .
                'headerDetail, detail, footerDetail, footerB, afterB, footerA, afterA, footerTotal, finish, ', $rep);
    }

    #[DataProvider('sumAndRangeProvider')]
    public function testSumAndRange($groupSource, $compSource, $key, $val, $row): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->group('A', $groupSource)
                ->compute('B', $compSource)
                ->sheet('C', $key, $val);
        $out = $rep->run([$row]);
        $this->assertSame(5, $rep->total['B']->sum());
        $this->assertSame(7, $rep->total['C']->range([6])->sum());
    }

    public static function sumAndRangeProvider(): array {
        $data = ['attr0' => 'groupAvalue', 'attr1' => 5, 'attr2' => 6, 'attr3' => 7];
        return ([
            [0, 1, 2, 3, array_values($data)],
            [0, 1, 2, 3, (object) array_values($data)],
        ]);
    }

    public function testFlowWithGroupChangesOnMultipleGroups(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class,]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->group('a', 'ga')
                ->group('b', 'gb')
                ->group('c', 'gc')
                ->compute('d', 'a2')
                ->run(null, false);
        // First row exectues all headers
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 31, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('start, headerTotal, beforeA, headerA, beforeB, headerB, beforeC, headerC, headerDetail, detail, ', $rep->out->pop());
        $this->assertSame(1, $rep->gc->items[1]->sum(0));
        $this->assertSame(1, $rep->gc->{1}->sum(0));
        $this->assertSame(1, $rep->gc->a->sum(0));
        $this->assertSame(1, $rep->gc->items[1]->sum('total'));

        // Next row change on gc executes only cFooter and cHeader
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('footerDetail, footerC, afterC, beforeC, headerC, headerDetail, detail, ', $rep->out->pop());

        // Next row change on ga executes all footers and headers
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 3, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('footerDetail, footerC, afterC, footerB, afterB, footerA, afterA, beforeA, headerA, beforeB, headerB, beforeC, headerC, headerDetail, detail, ', $rep->out->pop());

        // Next row change on gc executes only cFooter and cHeader
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('footerDetail, footerC, afterC, beforeC, headerC, headerDetail, detail, ', $rep->out->pop());

        // Next row change on gb executes gb and gc footers and headers
        $rep->next(['ga' => 12, 'gb' => 22, 'gc' => 99, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('footerDetail, footerC, afterC, footerB, afterB, beforeB, headerB, beforeC, headerC, headerDetail, detail, ', $rep->out->pop());

        // End of job
        $rep->end();
        $this->assertSame('footerDetail, footerC, afterC, footerB, afterB, footerA, afterA, footerTotal, finish, ', $rep->out->get());
        $this->assertSame(5 * 2, $rep->total->d->sum());
        $this->assertSame(5, $rep->rc->sum());
        $this->assertSame(2, $rep->gc->a->sum(0));      // Total a groups
        $this->assertSame(3, $rep->gc->b->sum(0));      // Total b groups
        $this->assertSame(5, $rep->gc->c->sum(0));      // Total c groups
    }
}
