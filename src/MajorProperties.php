<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * App is a collection of properties which act as global variables.
 * This decouples classes from main application class.
 * 
 * Please use only as readonly. Altering values might produce unpredictable results.
 * 
 * Declaring of getter and setter methods would decrease performance 
 *
 * @author Günter
 */
class MajorProperties {

    public $level = 0;             // The current execution level
    public $rc = [];               // Array of row counters
    public $gc = [];               // Array of group counters  
    public $detailLevel;           // Level for details 
    
    public function getLevel(int $level = null): int {
        $level = $level ?? ($this->level < $detailLevel) ? $htis->level : $this->detailLevel -1;
    }
}
