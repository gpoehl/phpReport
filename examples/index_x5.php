<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $bb;

    public function __construct() {

        $rep = (new Report($this));
        $rep->data('dat');
        $this->bb = $rep;
    }

}

$bb = new bbc();

$bb->bb->setCallOption(Report::CALL_ALWAYS_PROTOTYPE);
$bb->bb->run([['row1','dat'=>[[7,8,9]]]]);

$bb->bb->end();
echo $bb->bb->output;
//echo '<br>Sum von A = ' . $bb->bb->sum('A');
//echo '<br>Sum von E = ' . $bb->bb->sum('E');



