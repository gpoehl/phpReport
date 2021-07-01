<?php

declare(strict_types=1);

/**
 * Unit test of Report class.
 * For tests with multiple dimensions see ReportMultipleDimensionTest file
 */

use gpoehl\phpReport\CalculatorXS;
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\MajorProperties;
use gpoehl\phpReport\output\AbstractOutput;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{

    public function testBasics() {
        $rep = (new Report());
        $this->assertInstanceOf(MajorProperties::class, $rep->mp);
        $this->assertInstanceOf(Collector::class, $rep->rc);
        $this->assertInstanceOf(Collector::class, $rep->gc);
        $this->assertInstanceOf(Collector::class, $rep->total);
        $this->assertSame(Null, $rep->params);
        $this->assertInstanceOf(AbstractOutput::class, $rep->out);
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testConstructorParams($data) {
        $rep = (new Report(null, null, null, $data));
        $this->assertSame($data, $rep->params);
    }

    public function paramsProvider() {
        return [
            'Empty param' => [null],
            'One param' => ['myParam'],
            'One param as assoc array' => [['p1' => 'param1', 'p2' => 'param2', 'p3' => 'param3']],
        ];
    }

    /**
     * @dataProvider noDataProvider
     */
    public function testNoData($data, $expected) {
        $rep = (new Report($this->getBase(), ['actions' => ['noData' => 'nodata']]))
                ->setCallOption(Report::CALL_ALWAYS);
        $rep->run($data);
        $this->assertInstanceOf(CalculatorXS::class, $rep->rc[0]);
        $this->assertSame(['total' => 0], $rep->mp->groupLevel);
        $this->assertSame(0, $rep->mp->maxLevel);
        $this->assertSame('init, totalHeader, ' . $expected . 'totalFooter, close, ', $rep->out->get());
    }

    public function noDataProvider() {
        return [
            'Data set equals null' => [null, 'nodata, '],
            'Data set is an empty array' => [[], 'nodata, '],
        ];
    }

    public function testStringAsData() {
        $this->expectException(Error::class);
        (new Report($this->getBase()))->run('');
    }

    public function testNoGroupsDefined() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A']]);
        $this->assertSame('init, totalHeader, detail, totalFooter, close, ', $rep);
    }

    public function testRun_FinalizeIsFalse() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A']], false)
                ->end();
        $this->assertSame('init, totalHeader, detail, totalFooter, close, ', $rep);
    }

    public function testCallEndMethodWhenFinalizeIsTrueFails() {
        $this->expectException(Error::class);
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(['A'])               // run returns a string
                ->end();                   // method chaining works only on objects.
    }

    public function testChunkOfRowsWithOptionFinalizeIsFalseAndNext() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A'], ['B']], false);
        $rep->next(['C']);
        $rep->end();
        $this->assertSame('init, totalHeader, detail, detail, detail, totalFooter, close, ', $rep->out->get());
    }

    public function testNextForOneRowNoGroups() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);
        $rep->next(['A']);
        $rep->end();
        $this->assertSame('init, totalHeader, detail, totalFooter, close, ', $rep->out->get());
    }

    public function testGroupsOnOneRow() {
        $rep = (new Report($this->getBase()))
                ->group('a', 'firstGroup')
                ->group('b', 'secondGroup')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['firstGroup' => 'A', 'secondGroup' => 'X']]);
        $this->assertSame('init, totalHeader, aHeader, bHeader, ' . 
                'detailHeader, detail, detailFooter, bFooter, aFooter, totalFooter, close, ', $rep);
    }

    public function testPrototype() {
        $proto = $this->getPrototype();
        $rep = (new Report($proto))
                ->group('a', 0)
                ->setCallOption(Report::CALL_ALWAYS);
        $proto->report = $rep;
        $rep->run([['any value for group a', 'other value']]);

        $out = $rep->out->get();
        $this->assertStringContainsString('>init</th>', $out);
        $this->assertStringContainsString('>totalHeader</th>', $out);
        $this->assertStringContainsString('>groupHeader', $out);
        $this->assertStringContainsString('>aHeader', $out);
        $this->assertStringContainsString('>detail', $out);
        $this->assertStringContainsString('>groupFooter', $out);
        $this->assertStringContainsString('>aFooter', $out);
        $this->assertStringContainsString('>totalFooter</th>', $out);
        $this->assertStringContainsString('>close</th>', $out);
    }

    /**
     * @dataProvider GroupValue_sum_and_rsum_Provider
     */
    public function testGroupValue_sum_and_rsum($groupSource, $compSource, $key, $val, $row) {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_PROTOTYPE)
                ->group('A', $groupSource)
                ->compute('B', $compSource)
                ->sheet('C', $key, $val);
        $out = $rep->run([$row]);
        $this->assertStringContainsString('groupAvalue', $out);
        $this->assertSame(5, $rep->total['B']->sum());
        $this->assertSame(7, $rep->total['C']->range([6])->sum());
    }

    public function GroupValue_sum_and_rsum_Provider() {
        $data = ['attr0' => 'groupAvalue', 'attr1' => 5, 'attr2' => 6, 'attr3' => 7];
        return ([
            [0, 1, 2, 3, array_values($data)],
            [0, 1, 2, 3, (object) array_values($data)],
        ]);
    }

    /**
     * Make sure that no group method is called. One detail() method call per row. 
     */
    public function testNoGroups() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A'], ['B']]);
        $this->assertSame('init, totalHeader, detail, detail, totalFooter, close, ', $rep);
    }

    public function testFlowWithGroupChangesOnMultipleGroups() {
        $rep = (new Report($this->getBase()))
                ->group('a', 'ga')
                ->group('b', 'gb')
                ->group('c', 'gc')
                ->compute('d', 'a2')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);
        // First row exectues all headers
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 31, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('init, totalHeader, aHeader, bHeader, cHeader, detailHeader, detail, ', $rep->out->pop());
        $this->assertSame(1, $rep->gc->items[1]->sum(0));
        $this->assertSame(1, $rep->gc->{1}->sum(0));
        $this->assertSame(1, $rep->gc->a->sum(0));
        $this->assertSame(1, $rep->gc->items[1]->sum('total'));

        // Next row change on gc executes only cFooter and cHeader 
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('detailFooter, cFooter, cHeader, detailHeader, detail, ', $rep->out->pop());

        // Next row change on ga executes all footers and headers
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 3, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('detailFooter, cFooter, bFooter, aFooter, aHeader, bHeader, cHeader, detailHeader, detail, ', $rep->out->pop());

        // Next row change on gc executes only cFooter and cHeader 
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('detailFooter, cFooter, cHeader, detailHeader, detail, ', $rep->out->pop());

        // Next row change on gb executes gb and gc footers and headers
        $rep->next(['ga' => 12, 'gb' => 22, 'gc' => 99, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('detailFooter, cFooter, bFooter, bHeader, cHeader, detailHeader, detail, ', $rep->out->pop());

        // End of job
        $rep->end();
        $this->assertSame('detailFooter, cFooter, bFooter, aFooter, totalFooter, close, ', $rep->out->get());
        $this->assertSame(5 * 2, $rep->total->d->sum());
        $this->assertSame(5, $rep->rc->sum());
        $this->assertSame(2, $rep->gc->a->sum(0));      // Total a groups
        $this->assertSame(3, $rep->gc->b->sum(0));      // Total b groups
        $this->assertSame(5, $rep->gc->c->sum(0));      // Total c groups
    }

    /**
     * @dataProvider buildMethodsByGroupNameProvider
     */
    public function testBuildMethodsByGroupName($rule, $name, $header, $footer, $totalHeader, $totalFooter) {
        $rep = (new Report($this->getBase(),
                        ['actions' => [
                        'groupHeader' => 'header%',
                        'groupFooter' => 'footer%',
                        'totalHeader' => 'header%',
                        'totalFooter' => 'footer%'],
                    'buildMethodsByGroupName' => $rule
                        ]))
                ->group($name, 'ga')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['ga' => 1, 'gb' => 2]]);
        $this->assertSame("init, $totalHeader, $header, detailHeader, detail, detailFooter, $footer, $totalFooter, close, ", $rep);
    }

    public function buildMethodsByGroupNameProvider() {
        return [
            [true, 'a', 'headera', 'footera', 'headertotal', 'footertotal'],
            ['ucfirst', 'a', 'headerA', 'footerA', 'headerTotal', 'footerTotal'],
            [false, 'a', 'header1', 'footer1', 'header0', 'footer0'],
        ];
    }

    /**
     * @dataProvider headerAndFooterActionProvider
     */
    public function testHeaderAndFooterActions($headerAction, $footerAction, $expectedHeader, $expectedFooter) {
        $rep = (new Report($this->getBase()))
                ->group('a', 'ga')
                ->group('b', 'gb', null, $headerAction, $footerAction)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['ga' => 1, 'gb' => 2]]);
        $this->assertSame('init, totalHeader, aHeader, ' . $expectedHeader . 'detailHeader, detail, detailFooter, ' . $expectedFooter . 'aFooter, totalFooter, close, ', $rep);
    }

    public function headerAndFooterActionProvider() {
        return [
            'alternate header' => ['H, ', null, 'H, ', 'bFooter, '],
            'alternate header, no footer' => ['H, ', false, 'H, ', ''],
            'alternate footer' => [null, 'F, ', 'bHeader, ', 'F, '],
            'no header, alternate footer' => [false, 'F, ', '', 'F, '],
            'no header, noFooter' => [false, false, '', ''],
            'Closure header and footer' => [
                function ($val, $row) {
                    return (string) $val . (string) $row['ga'] . ', ';
                },
                function ($val, $row) {
                    return $val . $row['gb'] . ', ';
                },
                '21, ',
                '22, '
            ],
        ];
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
     * Other class which executes actions in other classes called from Report
     * @return anonymous class
     */
    public function getOtherClass() {
        return new class() {

            public function __call($name, $arguments) {
                return 'other_' . $name . ', ';
            }

            public static function staticCallMethod() {
                return 'other method called static, ';
            }

            public function callMethod() {
                return 'other method called on object, ';
            }
        };
    }

    /**
     * Test class to call prototyp method in report object. 
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
