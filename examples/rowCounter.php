<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class Example {

    public $rep;
    public $style = '<style>table {border-collapse:collapse;} table, th, td {border: 1px solid black;} th {font-weight: bold;}'
            . ' .num {text-align:right;}</style>';

    public function __construct() {
        $this->rep = (new Report($this))
                ->group('g1', 0)
                ->group('g2', 1)
                ->data(3)
                ->group('g3', 0);
        $this->rep->output = $this->style;
//        $this->rep->setCallOption(Report::CALL_PROTOTYPE);
    }

    public function detail($row, $rowKey) {
//        echo $this->rep->isFirst(0);
        $first = ($this->rep->isFirst()) ? 'true' : 'false';
        $tr = '<tr><td>%s</td><td class="num">%s</td></tr>';
        return "<hr><h1>Detail method: Row counter for row with key = $rowKey</h1>" .
                '<br>First = ' . $first .
                '<table>' .
                "<tr><th>Row with rowKey = $rowKey</th><th>Sum</th></tr>" .
                sprintf($tr, 'All row counters at current level', $this->rep->rc->sum()) .
                sprintf($tr, 'All row counters at grandTotal level', $this->rep->rc->sum(0)) .
                sprintf($tr, 'Row counter at grandTotal level and data level 0', $this->rep->rc->{0}->sum(0)) .
                sprintf($tr, 'Row counter at grandTotal level and data level 1', $this->rep->rc->{1}->sum(0)) .
                sprintf($tr, 'Row counter at grandTotal level and data level 0', $this->rep->rc->{0}->sum()) .
                sprintf($tr, 'Row counter at current group level and data level 1', $this->rep->rc->{1}->sum()) .
                '</table>';
    }
    public function footerg3($row, $rowKey){
        return '<br>Hello';
    }

}

$data = [
    ['G1-1', 'G2-1', 2, [['G3-1', 3], ['G3-1', 4], ['G3-1', 4]]],
    ['G1-1', 'G2-2', 4, [['G3_2', 5]]],
    ['G1-2', 'G2-2', 6, [['G3_1', 7]]],
    ['G1-2', 'G2-2', 8, [['G3_2', 9]]],
];
echo (new Example())->rep->run($data);