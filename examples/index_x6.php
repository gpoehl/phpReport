<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $bb;

    public function __construct() {
        $detail = function($row) {
            return '<br>&nbsp&nbsp&nbsp' . $row[0] . ' - I am a closure - ' . $row[1];
        };

        $rep = (new Report($this));
        $rep
                ->group('g1', 0)
                ->group('g2', 1)
                ->calculate('A', 3)
                ->calculate('B', 4)
                ->data(5)
                ->group('g3', 0)
                ->calculate('C', 3)
                ->data(4)
                ->calculate('E', 0)
                ->calculate('F', 1)
                ->sheet('G', [0, 1])
                ->sheet('H',
                        function($row, $rowKey) {return [$row[0] => $row[1] * 5];})
                        ;
        $this->bb = $rep;
    }

    public function g1Header($val, $row, $rowKey) {
        return '<br>Header G1: ' . $val
                . '<br>Sum A = ' . $this->bb->total->items['A']->sum();
    }

    public function g2Header($val, $row, $rowKey) {
        return '<br>&nbspHeader G2: ' . $val;
    }

    public function g3Header($val, $row, $rowKey) {
        return '<br>&nbsp&nbspHeader G3: ' . $val;
    }

    public function detail($row) {
//        echo '<br>Detail <br>';
//        var_dump($row);
        return '<br>&nbsp&nbsp&nbsp' . $row[0] . ' - ' . $row[1];
    }

    public function g3Footer($val, $row) {
        return '<br>&nbsp&nbspFooter G3: ' . $val;
    }

    public function g2Footer($val, $row) {
        return '<br>&nbspFooter G2: ' . $val;
    }

    public function g1Footer($val, $row) {
        return '<br>Footer G1: ' . $val;
    }

    public function noData() {
        return '<br>no Data at Dimension 0 by method';
    }

    public function noDataDim1($dim) {
        $r = $this->bb->getRow($dim - 1);
        return '<br>no Data_1 at dimension ' . $dim . ' for G1 = ' . $r[0];
    }

    public function noDataDim2($dim) {
        $r = $this->bb->getRow($dim - 1);
        return '<br><strong>no Data at dimension ' . $dim . 'for G1 = ' . $r[0] . '</strong>';
    }

}

$bb = new bbc();

$bb->bb->setCallOption(Report::CALL_ALWAYS_PROTOTYPE);
$bb->bb->run(null, false);
$bb->bb->next(
        ['A1', 'B1', 'Case 1', 2, 4, [
                ['D1', 'Case 1.1', 2, 4, [// dim 1
                        [5, 3], // dim2 = detail
                        [1, 2], // dim2 = detail
                    ],
                ],
                ['D2', 'Case 1.2', 3, 6, [// dim1   
                        [5, 9], // detail
                    ],
                ],
                ['D3', 'Case 1.3', 4, 7, null],
                ['D3', 'Case 1.4', 4, 7, null], // no group change
                ['D4', 'Case 1.5', 5, 8, [
                        [2, 9]
                    ]
                ]
            ]
]);


$bb->bb->next(['A1', 'B1', 'Case 1.6', 3, 5, null]); // Trigger group change on Dim 1 (Value D4)
$bb->bb->next(['A1', 'B1', 'Case 1.7', 4, 6, null]); // Trigger no group change. Render noData_n
$bb->bb->next(
        ['A2', 'B1', 'Case 2a', 4, 6, [
                ['X1', 'Case 2a.1', 6, 7, [
                        [20, 7], // detail
                    ],  
                ],
            ],
]);
$bb->bb->next(
        ['A2', 'B1', 'Case 2b', 4, 6, [
                ['X1', 'Case 2b.1', 6, 7, null], // dim1, details = null 
                ['X2', 'Case 2b.2', 4, 7, null], // dim1, no details 
                ['X3', 'Case 2b.3', 5, 9], // dim1, no details 
            ]
]);
$bb->bb->end();
echo $bb->bb->output;
//echo '<br>Sum von A = ' . $bb->bb->sum('A');
//echo '<br>Sum von E = ' . $bb->bb->sum('E');



