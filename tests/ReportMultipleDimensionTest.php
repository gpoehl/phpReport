<?php

declare(strict_types=1);

/**
 * Unit test of Report class. Handling of multiple data dimensions
 */

use gpoehl\phpReport\Report;
use gpoehl\phpReport\RuntimeOption;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportMultipleDimensionTest extends TestCase {

    #[DataProvider('noDataParamProvider')]
    public function testNoDataParameterOnNull($noData, $expected): void {
        $row = ['firstGroup' => 'A', 'b' => null];
        $this->runNoDataInDimension($row, $noData, $expected);
    }

    #[DataProvider('noDataParamProvider')]
    public function testNoDataParameterOnEmptyArray($noData, $expected): void {
        $row = ['firstGroup' => 'A', 'b' => []];
        $this->runNoDataInDimension($row, $noData, $expected);
    }

    public function runNoDataInDimension($row, $noDataAction, $expected): void {
        $out = self::getBase()->rep
                ->group('a', 'firstGroup')
                ->join('b', null , ['DimNoData' => $noDataAction])
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([$row]);
        $this->assertSame('start, headerTotal, beforeA, headerA, detail0, ' . $expected . 'footerA, afterA, footerTotal, finish, ', $out);
    }

    /**
     * Parameter to test all possible action methods when no data are given for dimension > 0.
     * @return array Array with indexed by an description. Data is the
     * value for the $noData parameter of the data() method and the expected result.
     */
    public static function noDataParamProvider(): array {
        return [
            'Call the default method' => [null, 'noData0, '],
            'No action will executed' => [false, ''],
            'Output is the given string' => [' no data in dim, ', ' no data in dim, '],
            'Call the given method' => ['myMethod', 'myMethod, '],
            'Execute the given closure' => [function ($row, $rowKey, int $dimID) {
                    return 'no data in dim ' . $dimID . ', ';
                }, 'no data in dim 0, '],
            'Call method in other class' => [[self::getOtherClass(), 'anyMethod'], 'Other_anyMethod, '],
        ];
    }

    #[DataProvider('rowDetailProvider')]
    public function testRowDetailParameterOfDataMethod($row, $rowDetail, $expected): void {
        $rep = self::getBase()
                ->rep
                ->join('B', null, ['DimDetail' =>$rowDetail])
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run(null, false);

        $this->assertInstanceOf(Report::class, $rep);
        $this->assertSame('start, headerTotal, ', $rep->out->get());
        $rep->out->delete();
        $rep->next($row, 'k1');
        $this->assertSame($expected, substr($rep->out->get(), 0, -2));
    }

    public static function rowDetailProvider(): array {
        $row = ['A', 'B' => [[1, 2, 3]]];
        return [
            'Call default method' => [$row, null, 'detail0, detail'],
            'No action' => [$row, false, 'detail'],
            'String' => [$row, 'string makes not much sense, ', 'string makes not much sense, detail'],
            'Call named method' => [$row, 'myMethod', 'myMethod, detail'],
            'Print arguments' => [$row, 'printArguments', ' arg0=' . json_encode($row) . ' arg1="k1" arg2=0 arg3=0, detail'],
            'Closure' => [$row, function ($row, $rowKey, $dimID) {
                    return ('rowKey = ' . $rowKey
                    . ' dimID = ' . $dimID
                    . ' row = ' . json_encode($row)
                    . ', ');
                }, 'rowKey = k1 dimID = 0 row = ' . json_encode($row) . ', detail'],
            'Call method in other class' => [$row, [self::getOtherClass(), 'anyMethod'], 'Other_anyMethod, detail'],
        ];
    }

     #[DataProvider('noGroupChangeProvider')]
    public function testNoGroupChangeParameterOfDataMethod($rows, $noGroupChange, $expected) : void{
        $rep = self::getBase()
                ->rep
                ->group('group1', 0)
                ->join('join1', 'C', ['DimNoGroupChange' =>$noGroupChange])
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run(null, false);
        // First row. Assertion in not really necessary.
        $rep->next($rows[0], 'k1');
        $this->assertSame('start, headerTotal, beforeGroup1, headerGroup1, detail0, detail, ', $rep->out->get());
        // Clear output and test second row which didn't trigger a group change.
        $rep->out->delete();
        $rep->next($rows[1], 'k2');
        $this->assertSame($expected, substr($rep->out->get(), 0, -2));
    }

    public static function noGroupChangeProvider(): array {
        $rows = [['A', 'B', 'C' => [[1, 3]]], ['A', 'X', 'C' => [[4, 5]]]];
        return [
//            'Call default method' => [$rows, null, 'noGroupChange0, detail0, detail'],  // Default actions throws error: Don't test it here.
            'No action' => [$rows, false, 'detail0, detail'],
            'String' => [$rows, "Row in dim %s didn't trigger a group change, ", "Row in dim 0 didn't trigger a group change, detail0, detail"],
            'Call named method' => [$rows, 'myMethod', 'myMethod, detail0, detail'],
            'Print arguments' => [$rows, 'printArguments', ' arg0=' . json_encode($rows[1]) . ' arg1="k2" arg2=0 arg3=1, detail0, detail'],
            'Closure' => [$rows, function ($row, $rowKey, $dimID) {
                    return ('rowKey = ' . $rowKey
                    . ' dimID = ' . $dimID
                    . ' row = ' . json_encode($row)
                    . ', ');
                }, 'rowKey = k2 dimID = 0 row = ' . json_encode($rows[1]) . ', detail0, detail'],
            'Call method in other class' => [$rows, [self::getOtherClass(), 'anyMethod'], 'Other_anyMethod, detail0, detail'],
        ];
    }

    /**
     * Since PHPUnit 10 warnings can't be tested
     */
//    public function testNoGroupChangeTriggersWarning() {
//        $this->expectWarning();
//        $this->getBase()
//                ->rep
//                ->group('group1', 0)
//                ->join('C', null, null, ['No Group Change', Action::WARNING])
//                ->run([['A', 'B', 'C' => [[1, 3]]], ['A', 'X', 'C' => [[4, 5]]]]);
//    }

    public function testNextDimOnObjectProperty() :void {
        $dimrows = [(object) ['D' => 11, 'E' => '1a'], (object) ['D' => 11, 'E' => '1b']];
        $row = (object) ['A' => 10, 'B' => $dimrows];
        $rep = $this->getBase()
                ->rep
                ->join('B')
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run([$row]);
        $this->assertSame('start, headerTotal, detail0, detail, detail, footerTotal, finish, ', $rep);
    }

    public function testSameDataDoesNotTriggerGroupChange() :void {
        $rep = $this->getBase(true)
                ->rep
                ->group('g1', 'A')
                ->group('g2', 'B')
                // Don't throw error.
                ->join('C', null, ['DimNoGroupChange' => 'noGroupChange%S'])
                ->group('g3', 'D')
                ->setRuntimeOption(RuntimeOption::Magic)
                ->run(null, false);
        $out = explode(', ', substr($rep->out->get(), 0, -2));
        $this->assertSame(2, count($out));
        $this->assertSame('start arg0=0', $out[0]);
        $this->assertSame('headerTotal arg0=0', $out[1]);

        $rep->out->delete();
        $dimrows = [['D' => 11, 'E' => '1a'], ['D' => 11, 'E' => '1b'], ['D' => 12, 'E' => '2a']];
        $row = ['A' => 10, 'B' => 20, 'C' => $dimrows];
        $rowKey = 'k1';
        $rep->next($row, $rowKey);
        $out = explode(', ', substr($rep->out->pop(), 0, -2));
        $this->assertSame('beforeG1 arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=1', $out[0]);
        $this->assertSame('headerG1 arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=1', $out[1]);
        $this->assertSame('beforeG2 arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=2', $out[2]);
        $this->assertSame('headerG2 arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=2', $out[3]);
        $this->assertSame('detail0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0 arg3=2', $out[4]);
        $this->assertSame('beforeG3 arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=3', $out[5]);
        $this->assertSame('headerG3 arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=3', $out[6]);
        $this->assertSame('headerDetail arg0=' . json_encode($dimrows[0])
                . ' arg1=0 arg2=3', $out[7]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[0])
                . ' arg1=0 arg2=3', $out[8]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[1])
                . ' arg1=1 arg2=3', $out[9]);
        $this->assertSame('footerDetail arg0=' . json_encode($dimrows[1])
                . ' arg1=1 arg2=3', $out[10]);
        $this->assertSame('footerG3 arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=3', $out[11]);
        $this->assertSame('afterG3 arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=3', $out[12]);
        $this->assertSame('beforeG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[13]);
        $this->assertSame('headerG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[14]);
        $this->assertSame('headerDetail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[15]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[16]);

        // no group change at dim 0 but same data in dim 1.
        $rowKey = 'k2';
        $rep->next($row, $rowKey);
        $out = explode(', ', substr($rep->out->pop(), 0, -2));
        $this->assertSame('noGroupChange0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0 arg3=3', $out[0]);
        $this->assertSame('detail0 arg0=' . json_encode($row)
                . ' arg1="' . $rowKey . '" arg2=0 arg3=3', $out[1]);
        $this->assertSame('footerDetail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[2]);
        $this->assertSame('footerG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[3]);
        $this->assertSame('afterG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[4]);
        $this->assertSame('beforeG3 arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=3', $out[5]);
        $this->assertSame('headerG3 arg0=11 arg1=' . json_encode($dimrows[0])
                . ' arg2=0 arg3=3', $out[6]);
        $this->assertSame('headerDetail arg0=' . json_encode($dimrows[0])
                . ' arg1=0 arg2=3', $out[7]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[0])
                . ' arg1=0 arg2=3', $out[8]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[1])
                . ' arg1=1 arg2=3', $out[9]);
        $this->assertSame('footerDetail arg0=' . json_encode($dimrows[1])
                . ' arg1=1 arg2=3', $out[10]);
        $this->assertSame('footerG3 arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=3', $out[11]);
        $this->assertSame('afterG3 arg0=11 arg1=' . json_encode($dimrows[1])
                . ' arg2=1 arg3=3', $out[12]);
        $this->assertSame('beforeG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[13]);
        $this->assertSame('headerG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[14]);
        $this->assertSame('headerDetail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[15]);
        $this->assertSame('detail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[16]);

        $rep->end();
        $out = explode(', ', substr($rep->out->pop(), 0, -2));
        $this->assertSame('footerDetail arg0=' . json_encode($dimrows[2])
                . ' arg1=2 arg2=3', $out[0]);
        $this->assertSame('footerG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[1]);
        $this->assertSame('afterG3 arg0=12 arg1=' . json_encode($dimrows[2])
                . ' arg2=2 arg3=3', $out[2]);
        $this->assertSame('footerG2 arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=2', $out[3]);
        $this->assertSame('afterG2 arg0=20 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=2', $out[4]);
        $this->assertSame('footerG1 arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=1', $out[5]);
        $this->assertSame('afterG1 arg0=10 arg1=' . json_encode($row)
                . ' arg2="' . $rowKey . '" arg3=1', $out[6]);
        $this->assertSame('footerTotal arg0=0', $out[7]);
        $this->assertSame('finish arg0=0', $out[8]);
    }

    /**
     * Basic class which executes actions called from Report
     * @return anonymous class
     */
    public static function getBase($printArguments = false) {

        return new class($printArguments) {

            public $rep;
            public $printArguments = false;

            public function __construct($printArguments = false) {
                $this->rep = new Report($this);
                $this->printArguments = $printArguments;
            }

            public function __call($name, $arguments) {
                $out = $name;
                if ($this->printArguments) {
                    $out .= substr($this->printArguments(... $arguments), 0, -2);
                }
                return $out . ', ';
            }

            public function printArguments(... $arguments) {
                $out = '';
                foreach ($arguments as $key => $argument) {
                    $out .= " arg$key="
                            . json_encode($argument);
                }
                return $out . ', ';
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
    public static function getOtherClass() {
        return new class() {

            public function __call($name, $arguments) {
                return 'Other_' . $name . ', ';
            }

            public function fullOutput($name, $arguments) {
                $out = 'Other_' . $name;
                if ($this->printArguments) {
                    foreach ($arguments as $key => $argument) {
                        $out .= " arg:$key="
                                . json_encode($argument);
                    }
                }
                return $out . ', ';
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

}
