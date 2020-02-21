<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $bb;

    public function __construct() {
        $rep = (new Report($this));
        $rep->group('g1', 0)
                ->group('g2', 1)
                ->data(3)
                ->group('g3', 0)
                 ->group('g4', 1)
                ->data(3);
        $this->bb = $rep;
    }
}

$bb = new bbc();

$bb->bb->setCallOption(Report::CALL_ALWAYS_PROTOTYPE);
$bb->bb->run(null, false);
$bb->bb->next(
        ['A1', 'B1', 'Case 1', [
                ['D1', 'E', 'Case 1.1', [// dim 1
                        [5, 3], // dim2 = detail
                        [1, 2], // dim2 = detail
                    ],
                ],
                ['D2', 'A', 'Case 1.2',  [// dim1   
                        [5, 9], // detail
                    ],
                ],
                ['D2', 'X','Case 1.3', [
                        [2, 9]
                    ]
                ]
            ]
]);


$bb->bb->next(
        ['A2', 'B1', 'Case 2a', [
                ['X1', 'Z',  'Case 2a.1',  [
                        [20, 7], // detail
                    ],  
                ],
            ],
]);

$bb->bb->end();
echo $bb->bb->output;
//echo '<br>Sum von A = ' . $bb->bb->sum('A');
//echo '<br>Sum von E = ' . $bb->bb->sum('E');



