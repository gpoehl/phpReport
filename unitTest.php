<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/vendor/autoload.php');

class bbc {
     public $config = ['actions' => [
            'init' => 'initxx',
            'totalHeader' => '%Header',
            'groupHeader' => '%Header',
            'detail' => 'detail',
            'groupFooter' => '%Footer',
            'totalFooter' => '%Footer',
            'close' => 'close',
            'noData' => ':<br><strong>No data found</strong><br>',
            'noData_n' => 'noDataDim%',
            'noGroupChange_n' => 'noGroupChange%'
        ]
         ];

    public function test1() {
        $row = ['A', 'B'];
        $rep = $this->getBase($this->config, true)
                ->rep
                ->data([$this->getOtherClass(), 'getNextDimData'], null, null, null, ['P1', 'P2'])
                ->setCallOption(Report::CALL_ALWAYS)
                ->run(null, false);
        $rep->next($row, 'K1');
    }

    /**
     * Basic class which executes actions called from Report
     * @return anonymous class
     */
    public function getBase($config) {

        return new class($config) {

            public $rep;

            public function __construct($config = null) {
                $this->rep = new Report($this, $config);
            }

            public function __call($name, $arguments) {
                return $name . ', ';
            }

            public static function staticCallMethod() {
                return 'my method called static, ';
            }

            public static function callMethod() {
                return 'my method called on object, ';
            }

            public function dataRow($row, $rowKey, $dimID) {
                return ('rowKey = ' . $rowKey
                        . ' dimID = ' . $dimID
                        . ' row = ' . json_encode($row)
                        );
            }

            public function getNextDimReturnsNull() {
                return null;
            }

             public function getNextDimData($row, $rowKey, $dimID, $param1, $param2) {
                return [
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ];
            }

            public function getNextDimCallRunAndNext($report, $row, $rowKey, int $dimID) {
                $report->run([['A'], ['B']]);
                $report->next(['C']);
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
                return 'other_' . $name . ', ';
            }

            public static function staticCallMethod() {
                return 'other method called static, ';
            }

            public function callMethod() {
                return 'other method called on object, ';
            }
            public function getNextDimData($row, $rowKey, $dimID, $param1, $param2) {
                return [
                    ['row1', $row[0], $param1],
                    ['row2', $row[0], $param2]
                ];
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

$bb = new bbc();
$class = $bb->test1();
echo $class->report->output;
//['A', 'B', 'data' => null], null, null, null, 'noData1');
////$bb->rep->run(
//////        null
////        [
////            ['G1-1', 'G2-1', 1, 2, 1,],
////            ['G1-1', 'G2-1', 1, 2, 1,],
////            ['G1-1', 'G2-2', 1, 2, 1,],
////            ['G1-2', 'G2-2', 1, 2, 1,]
////]
////);

echo '<br>Output:<br>';
//echo $bb->rep;
//echo $bb->rep->output;
