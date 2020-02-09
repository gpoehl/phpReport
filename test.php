<?php
declare(strict_types=1);
require(__DIR__ . '/vendor/autoload.php');

use gpoehl\phpReport\Collector;

 


$a = new Collector();
$executionStartTime = microtime(true);
for ($i = 1; $i<1000000; $i++){
        if (is_object($a)){
            
    }
}
$executionEndTime = microtime(true);
$usedTime = $executionEndTime - $executionStartTime;
echo '<br>usedTime: ' . $usedTime;

$executionStartTime = microtime(true);
for ($i = 1; $i<1000000; $i++){
        if ($a instanceof stdClass){
            
    }
}
$executionEndTime = microtime(true);
$usedTime = $executionEndTime - $executionStartTime;
echo '<br>usedTime: ' . $usedTime;
echo '<br>Done';
