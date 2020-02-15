<?php

declare(strict_types=1);

/**
 * Unit test of Report class. Handling of multiple data dimensions
 */
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ReportMultiDimensionTest extends TestCase {

    /**
     * @dataProvider noDataParamProvider
     */
    public function testNoDataParameterOfDataMethodWhereDataIsNull($noData, $expected) {
        $row = ['firstGroup' => 'A', 'b' => null];
        $this->runNoDataInDimension($row, $noData, $expected);
    }

    /**
     * @dataProvider noDataParamProvider
     */
    public function testNoDataParameterOfDataMethodWhereDataIsAnEmptyArray($noData, $expected) {
        $row = ['firstGroup' => 'A', 'b' => []];
        $this->runNoDataInDimension($row, $noData, $expected);
    }

    public function runNoDataInDimension($row, $noData, $expected) {
        $out = $this->getBase()->rep
                ->group('a', 'firstGroup')
                ->data('b', $noData)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([$row]);
        $this->assertSame('init<<>>totalHeader<<>>aHeader<<>>detail0<<>>' . $expected . 'aFooter<<>>totalFooter<<>>close<<>>', $out);
    }

    /**
     * Parmeter to test all possible action methods when no data are given for dimension > 0.
     * @return array Array with arrays. They have a description as key. Data is the 
     * value for the $noData parameter of the data() method and the expected result. 
     */
    public function noDataParamProvider(): array {
        return [
            'Call the default method' => [null, 'noDataDim0<<>>'],
            'No action will executed' => [false, ''],
            'Output is the given string' => [' no data in dim<<>>', ' no data in dim<<>>'],
            'Call the given method' => ['myMethod', 'myMethod<<>>'],
            'Execute the given closure' => [function ($dim) {
                    return 'no data in dim ' . $dim . '<<>>';
                }, 'no data in dim 0<<>>'],
            'Call method in other class' => [[$this->getOtherClass(), 'anyMethod'], 'Other_anyMethod<<>>'],
        ];
    }

    /**
     * @dataProvider rowDetailProvider
     */
    public function testRowDetailParameterOfDataMethod($row, $rowDetail, $expected) {
        $rep = $this->getBase()
                ->rep
                ->data('B', null, $rowDetail)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);

        $this->assertInstanceOf(Report::class, $rep);
        $this->assertSame('init<<>>totalHeader<<>>', $rep->output);
        $rep->output = '';
        $rep->next($row, 'k1');
        $this->assertSame($expected, substr($rep->output, 0, -4));
    }

    public function rowDetailProvider() {
        $row = ['A', 'B' => [[1, 2, 3]]];
        return [
            'Call default method' => [$row, null, 'detail0<<>>detail'],
            'No action' => [$row, false, 'detail'],
            'String' => [$row, 'string makes not much sense<<>>', 'string makes not much sense<<>>detail'],
            'Call named method' => [$row, 'myMethod', 'myMethod<<>>detail'],
            'Print arguments' => [$row, 'printArguments', ' arg0=' . json_encode($row) . ' arg1="k1" arg2=0<<>>detail'],
            'Closure' => [$row, function ($row, $rowKey, $dimID) {
                    return ('rowKey = ' . $rowKey
                            . ' dimID = ' . $dimID
                            . ' row = ' . json_encode($row)
                            . '<<>>');
                }, 'rowKey = k1 dimID = 0 row = ' . json_encode($row) . '<<>>detail'],
            'Call method in other class' => [$row, [$this->getOtherClass(), 'anyMethod'], 'Other_anyMethod<<>>detail'],
        ];
    }

    /**
     * @dataProvider noGroupChangeProvider
     */
    public function testNoGroupChangeParameterOfDataMethod($rows, $noGroupChange, $expected) {
        $rep = $this->getBase()
                ->rep
                ->group('group1', 0)
                ->data('C', null, null, $noGroupChange)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);
        // First row. Assertion in not really necessary.
        $rep->next($rows[0], 'k1');
        $this->assertSame('init<<>>totalHeader<<>>group1Header<<>>detail0<<>>detail<<>>', $rep->output);
        // Clear output and test second row which didn't trigger a group change.
        $rep->output = '';
        $rep->next($rows[1], 'k2');
        $this->assertSame($expected, substr($rep->output, 0, -4));
    }

    public function noGroupChangeProvider() {
        $rows = [['A', 'B', 'C' => [[1, 3]]], ['A', 'X', 'C' => [[4, 5]]]];
        return [
            'Call default method' => [$rows, null, 'noGroupChange0<<>>detail0<<>>detail'],
            'No action' => [$rows, false, 'detail0<<>>detail'],
            'String' => [$rows, "Row in my dim didn't trigger a group change<<>>", "Row in my dim didn't trigger a group change<<>>detail0<<>>detail"],
            'Call named method' => [$rows, 'myMethod', 'myMethod<<>>detail0<<>>detail'],
            'Print arguments' => [$rows, 'printArguments', ' arg0=' . json_encode($rows[1]) . ' arg1="k2" arg2=0<<>>detail0<<>>detail'],
            'Closure' => [$rows, function ($row, $rowKey, $dimID) {
                    return ('rowKey = ' . $rowKey
                            . ' dimID = ' . $dimID
                            . ' row = ' . json_encode($row)
                            . '<<>>');
                }, 'rowKey = k2 dimID = 0 row = ' . json_encode($rows[1]) . '<<>>detail0<<>>detail'],
            'Call method in other class' => [$rows, [$this->getOtherClass(), 'anyMethod'], 'Other_anyMethod<<>>detail0<<>>detail'],
        ];
    }

    public function testNoGroupChangeThrowsException() {
        $this->expectException(RuntimeException::class);
        $this->getBase()
                ->rep
                ->group('group1', 0)
                ->data('C', null, null, 'error:No Group Change')
                ->run([['A', 'B', 'C' => [[1, 3]]], ['A', 'X', 'C' => [[4, 5]]]]);
    }

    public function testNoGroupChangeTriggersWarning() {
        $this->expectException(\PHPUnit\Framework\Error\Notice::class);
        $this->getBase()
                ->rep
                ->group('group1', 0)
                ->data('C', null, null, 'warning:No Group Change')
                ->run([['A', 'B', 'C' => [[1, 3]]], ['A', 'X', 'C' => [[4, 5]]]]);
    }

    /**
     * @dataProvider DataSourcesReturnsDataSetProvider
     */
    public function testDataSourcesReturnsDataSet($row, $dataSource, $makeClosure = false, $passReport = false) {
        $rep = $this->getBase(true)
                ->rep;

        // Create closue to allow passing $rep and call run(). 
        if ($makeClosure) {
            $dataSource = function ($row, $rowKey, $dimID, $param1, $param2) use ($rep) {
                $rep->run([
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                        ]
                );
                return false;
            };
        }
        $parameters = ($passReport) ? [$rep, 'P1', 'P2'] : ['P1', 'P2'];
        $rep->data($dataSource, null, null, null, $parameters)
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);

        $rep->next($row, 'K1');
        $out = explode('<<>>', substr($rep->output, 0, -4));
        $this->assertSame('init', $out[0]);
        $this->assertSame('totalHeader', $out[1]);
        $this->assertSame('detail0 arg0=' . json_encode($row) . ' arg1="K1" arg2=0', $out[2]);
        $this->assertSame('detail arg0=' . json_encode(['row1', 'A', 'P1']) . ' arg1=0', $out[3]);
        $this->assertSame('detail arg0=' . json_encode(['row2', 'A', 'P2']) . ' arg1=1', $out[4]);
    }

    public function DataSourcesReturnsDataSetProvider() {
        $row = ['A', 'B'];
        return [
            'Method in target' => [$row, ['getNextDimData']],
            'Method in class of row' => [$this->getRowAsObject($row), ['getNextDimData']],
            'Method in other class' => [$row, [$this->getOtherClass(), 'getNextDimData']],
            'Closure' => [
                $row
                , function ($row, $rowKey, $dimID, $param1, $param2) {
                    return ([
                        ['row1', $row[0], $param1],
                        ['row2', $row[0], $param2]
                            ]
                            );
                }
            ],
            'Method in target calls run' => [$row, ['CallRunWithNextDimData']],
            'Method in class of row calls run' => [$this->getRowAsObject($row), ['CallRunWithNextDimData'], false, true],
            'Method in other class calls run' => [$row, [$this->getOtherClass(), 'CallRunWithNextDimData'], false, true],
            'Closure calls run' => [$row, null, true]
        ];
    }

    public function testNextDimOnObjectProperty() {
        $dimrows = [(object) ['D' => 11, 'E' => '1a'], (object) ['D' => 11, 'E' => '1b']];
        $row = (object) ['A' => 10, 'B' => $dimrows];
        $rep = $this->getBase()
                ->rep
                ->data('B')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run([$row]);
        $this->assertSame('init<<>>totalHeader<<>>detail0<<>>detail<<>>detail<<>>totalFooter<<>>close<<>>', $rep);
    }

    public function testNoDataDoesNotTriggerGroupChange() {
        $rep = $this->getBase(true)
                ->rep
                ->group('g1', 'A')
                ->group('g2', 'B')
                ->data('C')
                ->group('g3', 'D')
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);

        $out = explode('<<>>', substr($rep->output, 0, -4));
        $this->assertSame(2, count($out));
        $this->assertSame('init', $out[0]);
        $this->assertSame('totalHeader', $out[1]);

        $rep->output = '';
        $dimrows = [['D' => 11, 'E' => '1a'], ['D' => 11, 'E' => '1b'], ['D' => 12, 'E' => '2a']];
        $row = ['A' => 10, 'B' => 20, 'C' => $dimrows];
        $rowKey = 'k1';
        $rep->next($row, $rowKey);
        $out = explode('<<>>', substr($rep->output, 0, -4));
        $this->assertSame(9, count($out));
        $this->assertSame('g1Header arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=0', $out[0]);
        $this->assertSame('g2Header arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=0', $out[1]);
        $this->assertSame('detail0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0', $out[2]);
        $this->assertSame('g3Header arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=1', $out[3]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[0])
                . ' arg1=0', $out[4]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[1])
                . ' arg1=1', $out[5]);
        $this->assertSame('g3Footer arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=1', $out[6]);
        $this->assertSame('g3Header arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=1', $out[7]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[2])
                . ' arg1=2', $out[8]);

        // no group change at dim 0 but same data in dim 1.
        $rep->output = '';
        $rowKey = 'k2';
        $rep->next($row, $rowKey);
        $out = explode('<<>>', substr($rep->output, 0, -4));
        $this->assertSame(9, count($out));
        $this->assertSame('noGroupChange0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0', $out[0]);
        $this->assertSame('detail0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0', $out[1]);
        // Footer for last row in dim 1 of previous dim 0 row
        $this->assertSame('g3Footer arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=1', $out[2]);
        $this->assertSame('g3Header arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=1', $out[3]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[0])
                . ' arg1=0', $out[4]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[1])
                . ' arg1=1', $out[5]);
        $this->assertSame('g3Footer arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=1', $out[6]);
        $this->assertSame('g3Header arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=1', $out[7]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[2])
                . ' arg1=2', $out[8]);

        $rep->output = '';
        $rep->end();
        $out = explode('<<>>', substr($rep->output, 0, -4));
        $this->assertSame(5, count($out));
        $this->assertSame('g3Footer arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=1', $out[0]);
        $this->assertSame('g2Footer arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=0', $out[1]);
        $this->assertSame('g1Footer arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=0', $out[2]);
        $this->assertSame('totalFooter', $out[3]);
        $this->assertSame('close', $out[4]);
    }

    /**
     * Basic class which executes actions called from Report
     * @return anonymous class
     */
    public function getBase($printArguments = false) {

        return new class($printArguments) {
            // Make sure to have a defined set of actions not influenced by defaults
            // of confic class 
            public $config = ['actions' => [
                    'init' => 'init',
                    'totalHeader' => '%Header',
                    'groupHeader' => '%Header',
                    'detail' => 'detail',
                    'groupFooter' => '%Footer',
                    'totalFooter' => '%Footer',
                    'close' => 'close',
                    'noData' => ':<br><strong>No data found</strong><br>',
                    'noData_n' => 'noDataDim%',
                    'noGroupChange_n' => 'noGroupChange%',
                    'data_n' => 'detail%'
                ]
            ];
            public $rep;
            public $printArguments = false;

            public function __construct($printArguments = false) {
                $this->rep = new Report($this, $this->config);
                $this->printArguments = $printArguments;
            }

            public function __call($name, $arguments) {
                $out = $name;
                if ($this->printArguments) {
                    $out .= substr($this->printArguments(... $arguments), 0, -4);
                }
                return $out . '<<>>';
            }

            public function printArguments(... $arguments) {
                $out = '';
                foreach ($arguments as $key => $argument) {
                    $out .= " arg$key="
                            . json_encode($argument);
                }
                return $out . '<<>>';
            }

            public function getNextDimReturnsNull() {
                return null;
            }

            public function getNextDimData($row, $rowKey, int $dimID, $param1, $param2) {
                return [
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ];
            }

            public function CallRunWithNextDimData($row, $rowKey, int $dimID, $param1, $param2) {
                $this->rep->run([
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ]);
                return false;
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
                return 'Other_' . $name . '<<>>';
            }

            public function fullOutput($name, $arguments) {
                $out = 'Other_' . $name;
                if ($this->printArguments) {
                    foreach ($arguments as $key => $argument) {
                        $out .= " arg:$key="
                                . json_encode($argument);
                    }
                }
                return $out . '<<>>';
            }

            public function getNextDimData($row, $rowKey, $dimID, $param1, $param2) {
                return [
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ];
            }

            public function CallRunWithNextDimData($row, $rowKey, int $dimID, $report, $param1, $param2) {
                $report->run([
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ]);
                return false;
            }
        };
    }

    /**
     * Class which returns data related to object
     * @return anonymous class
     */
    public function getRowAsObject($row) {
        return new Class($row) {

            public $row;
            public $test = 'hallo';

            public function __construct($row) {
                $this->row = $row;
            }

            public function getNextDimData($param1, $param2) {
                return [
                    ['row1', $this->row[0], $param1],
                    ['row2', $this->row[0], $param2]
                ];
            }

            public function CallRunWithNextDimData($report, $param1, $param2) {
                $report->run([
                    ['row1', $this->row[0], $param1],
                    ['row2', $this->row[0], $param2]
                ]);
                return false;
            }
        };
    }

}
