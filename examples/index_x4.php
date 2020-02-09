<?php

use gpoehl\phpReport\Report;

require(__DIR__ . '/../vendor/autoload.php');

class bbc {

    public $bb;
    public $a;
    public $b;
    public $c;

    public function __construct() {
        $this->bb = (new Report($this));
        $this->bb->group('G1', 0)
                ->group('G2', 1)
//                ->calculate('A', 2, Report::REGULAR)
//                ->calculate('B', 3, Report::REGULAR)
//                ->calculate('C', 4, Report::REGULAR)
                ->calculate('A', 2)
                ->calculate('B', 3)
                ->calculate('C', 4)
        ;
    }

    public function __call($name, $arguments) {
        return '<br>Called method ' . $name;
    }

}

$executionStartTime = microtime(true);
$bb = new bbc();
$bb->bb->run(null, false);
//$bb->bb->setCallOption(Backbone::CALL_ALWAYS_PROTOTYPE);
for ($i2 = 1; $i2 <= 100; $i2++) {
    for ($i1 = 1; $i1 <= 100; $i1++) {
        for ($i = 1; $i <= 100; $i++) {
            $bb->bb->next(["G1-$i2", "G2-$i1", $i2, $i1, $i]);
        }
    }
}
$bb->bb->end();

echo $bb->bb->output;
$executionEndTime = microtime(true);
$usedTime = $executionEndTime - $executionStartTime;
echo '<br>usedTime: ' . $usedTime;
//echo '<br>handle row get Values usedTime: ' .$bb->bb->handleRowGetValuesUsedTime;
//echo '<br>checkGroupHasChanged usedTime: ' .$bb->bb->checkGroupHasChangedUsedTime;
