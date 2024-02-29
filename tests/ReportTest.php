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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{

    public function testBasics() :void {
        $rep = (new Report());
        $this->assertInstanceOf(Collector::class, $rep->rc);
        $this->assertInstanceOf(Collector::class, $rep->gc);
        $this->assertInstanceOf(Collector::class, $rep->total);
        $this->assertSame(Null, $rep->params);
        $this->assertInstanceOf(AbstractOutput::class, $rep->out);
    }

     #[DataProvider('paramsProvider')]
    public function testConstructorParams($data) : void{
        $rep = (new Report(null, null, null, $data));
        $this->assertSame($data, $rep->params);
    }

    public static function paramsProvider() :array {
        return [
            'Empty param' => [null],
            'One param' => ['myParam'],
            'One param as assoc array' => [['p1' => 'param1', 'p2' => 'param2', 'p3' => 'param3']],
        ];
    }

     #[DataProvider('noDataProvider')]
    public function testNoData($data, $expected) :void {
        $rep = (new Report($this->getBase(), ['actions' => ['NoData' => 'nodata']]))
                ->setRuntimeOption(RuntimeOption::Magic);
        $rep->run($data);
        $this->assertSame('start, headerTotal, ' . $expected . 'footerTotal, finish, ', $rep->out->get());
    }

    public static function noDataProvider() :array {
        return [
            'Data set equals null' => [null, 'nodata, '],
            'Data set is an empty array' => [[], 'nodata, '],
        ];
    }

    public function testStringAsData() :void {
        $this->expectException(Error::class);
        (new Report($this->getBase()))->run('');
    }

    public function testNoGroupsDefined() :void  {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([['A']]);
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep);
    }

    public function testRun_CompletedIsFalse():void  {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([['A']], false)
                ->end();
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep);
    }

    public function testCallEndMethodWhenFinalizeIsTrueFails() :void {
        $this->expectException(Error::class);
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run(['A'])               // run returns a string
                ->end();                   // method chaining works only on objects.
    }

    public function testChunkOfRowsWithOptionFinalizeIsFalseAndNext() :void {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([['A'], ['B']], false);
        $rep->next(['C']);
        $rep->end();
        $this->assertSame('start, headerTotal, detail, detail, detail, footerTotal, finish, ', $rep->out->get());
    }

    public function testNextForOneRowNoGroups():void  {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run(null, false);
        $rep->next(['A']);
        $rep->end();
        $this->assertSame('start, headerTotal, detail, footerTotal, finish, ', $rep->out->get());
    }

    public function testGroupsWithOneDataRow():void  {
        $rep = (new Report($this->getBase()))
                ->group('a', 'firstGroup')
                ->group('b', 'secondGroup')
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([['firstGroup' => 'A', 'secondGroup' => 'X']]);
        $this->assertSame('start, headerTotal, beforeA, headerA, beforeB, headerB, ' .
                'headerDetail, detail, footerDetail, footerB, afterB, footerA, afterA, footerTotal, finish, ', $rep);
    }

    public function testPrototype():void  {
        $proto = $this->getPrototype();
        $rep = (new Report($proto))
                ->group('a', 0)
                ->setRuntimeOption(RuntimeOption::Magic);
        $proto->report = $rep;
        $rep->run([['any value for group a', 'other value']]);

        $out = $rep->out->get();
        $this->assertStringContainsString('>start</th>', $out);
        $this->assertStringContainsString('>headerTotal</th>', $out);
        $this->assertStringContainsString('>beforeA', $out);
        $this->assertStringContainsString('>headerA', $out);
        $this->assertStringContainsString('>detail', $out);
        $this->assertStringContainsString('>footerDetail', $out);
        $this->assertStringContainsString('>footerA', $out);
        $this->assertStringContainsString('>footerTotal</th>', $out);
        $this->assertStringContainsString('>finish</th>', $out);
    }

      #[DataProvider('GroupValue_sum_and_rsum_Provider')]
    public function testGroupValue_sum_and_rsum($groupSource, $compSource, $key, $val, $row) :void {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->group('A', $groupSource)
                ->compute('B', $compSource)
                ->sheet('C', $key, $val);
        $out = $rep->run([$row]);
        $this->assertStringContainsString('groupAvalue', $out);
        $this->assertSame(5, $rep->total['B']->sum());
        $this->assertSame(7, $rep->total['C']->range([6])->sum());
    }

    public static function GroupValue_sum_and_rsum_Provider() :array {
        $data = ['attr0' => 'groupAvalue', 'attr1' => 5, 'attr2' => 6, 'attr3' => 7];
        return ([
            [0, 1, 2, 3, array_values($data)],
            [0, 1, 2, 3, (object) array_values($data)],
        ]);
    }

    /**
     * Make sure that no group method is called. One detail() method call per row.
     */
    public function testNoGroups() :void {
        $rep = (new Report($this->getBase()))
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([['A'], ['B']]);
        $this->assertSame('start, headerTotal, detail, detail, footerTotal, finish, ', $rep);
    }

    public function testFlowWithGroupChangesOnMultipleGroups() :void {
        $rep = (new Report($this->getBase()))
                ->group('a', 'ga')
                ->group('b', 'gb')
                ->group('c', 'gc')
                ->compute('d', 'a2')
                ->setRuntimeOption(RuntimeOption::Magic)
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


    /**
     * Basic class which executes actions called from Report
     * @return anonymous class
     */
    public function getBase() {
        return new class() {

            public function __call($name, $arguments) {
                return $name . ', ';
            }

            public static function staticCallMethod() {
                return 'my method called static, ';
            }

            public static function callMethod() {
                return 'my method called on object, ';
            }
        };
    }

    /**
     * Test class calls prototyp method in report object.
     */
    public function getPrototype() {
        return new class() {

            public $report;

            public function __call($name, $arguments) {
                return $this->report->prototype();
            }
        };
    }

}
