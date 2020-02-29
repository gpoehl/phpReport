<?php

declare(strict_types=1);

/**
 * Unit test of Report class.
 * For tests with multiple dimensions see ReportMultipleDimensionTest file
 */
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\CalculatorXS;
use gpoehl\phpReport\MajorProperties;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase {

    public function testConstructor() {
        $rep = (new Report($this->getBase(), [
                    'actions' => ['noData' => 'nodata'],
                    'userConfig' => 'myConfig',
        ]));
        $this->assertInstanceOf(MajorProperties::class, $rep->mp);
        $this->assertInstanceOf(Collector::class, $rep->rc);
        $this->assertInstanceOf(Collector::class, $rep->gc);
        $this->assertInstanceOf(Collector::class, $rep->total);
        $this->assertSame('myConfig', $rep->userConfig);
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
        $this->assertSame(0, $rep->mp->lastLevel);
        $this->assertSame('init, totalHeader, ' . $expected . 'totalFooter, close, ', $rep->output);
    }

    public function noDataProvider() {
        return [
            'Data set equals null' => [null, 'nodata, '],
            'Data set is an empty array' =>[[], 'nodata, '],
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
                 ->data('array')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A']], false)
                ->end();
        $this->assertSame('init, totalHeader, detail, totalFooter, close, ', $rep);
    }

    public function testCallEndMethodWhenFinalizeIsTrueFails() {
        $this->expectException(Error::class);
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(['A'])               // run returns a string
                ->end();                   // method chaining works only on objects.
    }

    public function testChunkOfRowsWithOptionFinalizeIsFalseAndNext() {
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['A'], ['B']], false)
                ->next(['C'])
                ->end();
        $this->assertSame('init, totalHeader, detail, detail, detail, totalFooter, close, ', $rep);
    }

    public function testNextForOneRowNoGroups() {
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false)
                ->next(['A'])
                ->end();
        $this->assertSame('init, totalHeader, detail, totalFooter, close, ', $rep);
    }
    
    public function testDimensionMustBeDeclaredBeforeGroup(){
         $this->expectExceptionMessage("Before calling the group() method for 'a' the data() method must be called.");
         $rep = (new Report($this->getBase()))
                ->group('a');
    }
     public function testDimensionMustBeDeclaredBeforeCalculate(){
         $this->expectExceptionMessage("Before calling the calculate() method for 'a' the data() method must be called.");
         $rep = (new Report($this->getBase()))
                ->calculate('a');
    }
     public function testDimensionMustBeDeclaredBeforeSheet(){
         $this->expectExceptionMessage("Before calling the sheet() method for 'a' the data() method must be called.");
         $rep = (new Report($this->getBase()))
                ->sheet('a', 'b');
    }

    public function testGroupsOnOneRow() {
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->group('a', 'firstGroup')
                ->group('b', 'secondGroup')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['firstGroup' => 'A', 'secondGroup' => 'X']]);
        $this->assertSame('init, totalHeader, aHeader, bHeader, detail, bFooter, aFooter, totalFooter, close, ', $rep);
    }

    /**
     * @dataProvider callOptionsProvider
     */
    public function testActionTypes($option, $ex1, $ex2, $ex3, $ex4, $ex5) {
        $rep = (new Report($this->getBase(), [
                    'actions' => [
                        'init' => 'init', // normal method
                        'totalHeader' => [$this->getOtherClass(), 'staticCallMethod'], // Static callable
                        'noData' => [$this->getOtherClass(), 'callMethod'], // Object callable
                        'totalFooter' => 'callMethod', // Normal method call
                        'close' => function() {
                            return 'closure';                           // Closure
                        },
                    ]]))
                                 ->data('array')
                ->setCallOption($option)
                ->run(null);
        $this->assertStringContainsString($ex1, $rep);
        $this->assertStringContainsString($ex2, $rep);
        $this->assertStringContainsString($ex3, $rep);
        $this->assertStringContainsString($ex4, $rep);
        $this->assertStringContainsString($ex5, $rep);
    }

    public function callOptionsProvider() {
        return [
            [Report::CALL_ALWAYS, 'init, ', 'other method called static, ', 'other method called on object, ', 'my method called on object, ', 'closure'],
            [Report::CALL_PROTOTYPE, '>init</th>', 'other method called static, ', 'other method called on object, ', 'my method called on object, ', 'closure'],
            [Report::CALL_ALWAYS_PROTOTYPE, '>init</th>', '>totalHeader', '>noData</th>', '>totalFooter', '>close</th>'],
        ];
    }

    // No action will be executed when method call is requested and
    // method does not exist in owner class
    public function testNotExistingMethods() {
        $rep = (new Report($this->getBase(), ['actions' => [
                        'init' => 'init', // normal method
                        'totalHeader' => [$this->getOtherClass(), 'staticCallMethod'], // Static callable
                        'noData' => [$this->getBase(), 'callMethod'], // Object callable
                        'totalFooter' => 'callMissing', // Missing method forces prototype
                        'close' => function() {
                            return 'closure';                           // Closure
                        },
                    ]]))
                                 ->data('array')
                ->setCallOption(Report::CALL_EXISTING)
                ->run(null);
        $this->assertStringNotContainsString('init', $rep);
        $this->assertStringContainsString('other method called static', $rep);
        $this->assertStringContainsString('my method called on object', $rep);
        $this->assertStringNotContainsString('totalFooter', $rep);
        $this->assertStringContainsString('closure', $rep);
    }

    public function testPrototype() {
        $proto = $this->getPrototype();
        $rep = (new Report($proto))
                 ->data('array')
                ->group('a', 0)
                ->setCallOption(Report::CALL_ALWAYS);
        $proto->report = $rep;
        $rep->run([['group a Value', 'X']]);

        $out = $rep->output;
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
    public function testGroupValue_sum_and_rsum($a0, $a1, $a2, $row, $handler) {
        $rep = (new Report($this->getBase()))
                 ->data($handler)
                ->setCallOption(Report::CALL_PROTOTYPE)
                ->group('A', $a0)
                ->calculate('B', $a1)
                ->sheet('C', $a2);
        $out = $rep->run([$row]);
        $this->assertStringContainsString('groupAvalue', $out);
        $this->assertSame(5, $rep->total['B']->sum());
        $this->assertSame(7, $rep->total['C']->rsum(6));
    }

    public function GroupValue_sum_and_rsum_Provider() {
        $data = ['attr0' => 'groupAvalue', 'attr1' => 5, 'attr2' => 6, 'attr3' => 7];
        return ([
            [0, 1, [2 , 3], array_values($data), 'array'],
            ['attr0', 'attr1', ['attr2', 'attr3'], $data, 'array'],
            [
                function($row) {
                    return $row['attr0'];
                },
                function($row) {
                    return $row['attr1'];
                },
                function($row) {
                    return [$row['attr2'] => $row['attr3']];
                },
                $data, 'array'],
            [0, 1, [2, 3], (object) array_values($data), 'object'],
            ['attr0', 'attr1', ['attr2' , 'attr3'], (object) ($data), 'object'],
            [
                function($row) {
                    return $row->attr0;
                },
                function($row) {
                    return $row->attr1;
                },
                function($row) {
                    return [$row->attr2 => $row->attr3];
                },
                (object) $data, 'object'],
                ]
                );
    }

    /**
     * Make sure that no group method is called. One detail() method call per row. 
     */
    public function testNoGroups() {
        $rep = (new Report($this->getBase()))
                ->setCallOption(Report::CALL_ALWAYS)
                 ->data('array')
                ->run([['A'], ['B']]);
        $this->assertSame('init, totalHeader, detail, detail, totalFooter, close, ', $rep);
    }

    public function testFlowWithGroupChangesOnMultipleGroups() {
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->group('a', 'ga')
                ->group('b', 'gb')
                ->group('c', 'gc')
                ->calculate('d', 'a2')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);
        // First row exectues all headers
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 31, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('init, totalHeader, aHeader, bHeader, cHeader, detail, ', $rep->output);
        $this->assertSame(1, $rep->gc->items[1]->sum(0));
        $this->assertSame(1, $rep->gc->{1}->sum(0));
        $this->assertSame(1, $rep->gc->a->sum(0));
        $this->assertSame(1, $rep->gc->items[1]->sum('total'));

        // Next row change on gc executes only cFooter and cHeader 
        $rep->output = null;
        $rep->next(['ga' => 11, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('cFooter, cHeader, detail, ', $rep->output);

        // Next row change on ga executes all footers and headers
        $rep->output = null;
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 3, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('cFooter, bFooter, aFooter, aHeader, bHeader, cHeader, detail, ', $rep->output);

        // Next row change on gc executes only cFooter and cHeader 
        $rep->output = null;
        $rep->next(['ga' => 12, 'gb' => 21, 'gc' => 32, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('cFooter, cHeader, detail, ', $rep->output);

        // Next row change on gb executes gb and gc footers and headers
        $rep->output = null;
        $rep->next(['ga' => 12, 'gb' => 22, 'gc' => 99, 'a1' => 'a', 'a2' => 2]);
        $this->assertSame('cFooter, bFooter, bHeader, cHeader, detail, ', $rep->output);

        // End of job
        $rep->output = null;
        $rep->end();
        $this->assertSame('cFooter, bFooter, aFooter, totalFooter, close, ', $rep->output);
        $this->assertSame(5 * 2, $rep->total->d->sum());
        $this->assertSame(5, $rep->rc->sum());
        $this->assertSame(2, $rep->gc->a->sum(0));      // Total a groups
        $this->assertSame(3, $rep->gc->b->sum(0));      // Total b groups
        $this->assertSame(5, $rep->gc->c->sum(0));      // Total c groups
    }

    /**
     * @dataProvider headerAndFooterActionProvider
     */
    public function testHeaderAndFooterActions($headerAction, $footerAction, $expectedHeader, $expectedFooter) {
        $rep = (new Report($this->getBase()))
                 ->data('array')
                ->group('a', 'ga')
                ->group('b', 'gb', $headerAction, $footerAction)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([['ga' => 1, 'gb' => 2]]);
        $this->assertSame('init, totalHeader, aHeader, ' . $expectedHeader . 'detail, ' . $expectedFooter . 'aFooter, totalFooter, close, ', $rep);
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
