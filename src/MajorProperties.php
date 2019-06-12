<?php
/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Major properties to be passed to cumulator and collector classes.
 * Please use only as readonly. Altering values might produce unpredictable results.
 */
class MajorProperties {

    public $level = 0;          // The current execution level
    public $rc;                 // Collector object of row counters
    public $gc;                 // Collector object of group counters  
    public $t;                 // Collector object of sum and sheet cumulators  
    public $groupLevel = [];    // Holds only the group level by groupname.
    public $detailLevel;        // Level for details 

    /**
     * Get the nurmeric group level.
     * Deepest group level is the level above detail level. 
     * @param type $level When level is null the actual level wil be returned.
     * When the actual level is the deail level the level above will be returned.
     * If $level is a string then the numeric level of the group will be returnd.
     * @return int The numeric group level.
     */
    public function getLevel($level = null): int {
        if ($level === null) {
            return ($this->level < $this->detailLevel) ? $this->level : $this->detailLevel - 1;
        }
        if (is_numeric($level)) {
            return $level;
        }
        if (isset($this->groupLevel[$level])) {
            return $this->groupLevel[$level];
        }
        trigger_error("Group '$level' does not exist.", E_USER_NOTICE);
    }

}
