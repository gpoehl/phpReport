<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $rep;

    public function __construct() {

        $this->rep = (new Report($this))
                ->setCallOption(Report::CALL_ALWAYS);
    }

    public static function staticCallMethod() {
        return 'Method called static, ';
    }

    public static function callMethod() {
        return 'Method called on object, ';
    }

    public function __call($name, $arguments) {
//        return $this->rep->prototype();
        return '<br>Called method: ' . $name;
    }

}

$bb = new bbc();
$x = $bb->rep->run([['A'], ['B']], false);
$x->next(['C']);
$x->end();
$bb->rep->run([['x', null]]);
//$bb->rep->run([['firstGroup' => 'A']]);

echo '<br>Output:<br>';
echo $bb->rep->output;
