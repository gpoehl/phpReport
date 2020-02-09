<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $rep;

    public function __construct() {
        $this->rep = (new Report($this, ['actions' => ['noData' => ':noData']]))
//                ->setCallOption(Report::CALL_ALWAYS)
                ->setCallOption(Report::CALL_ALWAYS_PROTOTYPE)
                ->group('g1', 0)
                ->group('g2', 1)
                ->calculate('A', 2)
                ->calculate('B', 3)
        ;
    }

    public function __call($name, $arguments) {
        return '<br>Called method: ' . $name;
    }

}

$bb = new bbc();
$bb->rep->run(
        [
            ['G1-1', 'G2-1', 1, 2, 1,],
            ['G1-1', 'G2-1', 1, 2, 1,],
            ['G1-2', 'G2-2', null, 2, 1,],
            ['G1-2', 'G2-2', null, 2, 1,]
        ]
);

echo '<br>Output:<br>';
echo $bb->rep->output;


