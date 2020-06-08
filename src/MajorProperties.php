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
    public $total;              // Collector object of sum and sheet cumulators  
    /** @var int[] Group level indexed by group name. */
    public $groupLevel = [];    
    public $maxLevel = 0;      // The last group level 

    /**
     * Get the group level.
     * Note: Detail level has no level. The lastLevel will be returned for detail level. 
     * @param mixed $level When level is null the actual level wil be returned.
     * If $level is a string having the group name then the group level will be returnd.
     * When $level is negative it will be substracted from the current level.
     * @return int The group level.
     */
    public function getLevel($level = null): int {
        if ($level === null) {
            return ($this->level < $this->maxLevel) ? $this->level : $this->maxLevel;
        }
        // Substract level when negative
        if (is_numeric($level)) {
            return ($level < 0)? $this->level + $level : $level;
        }
        if (isset($this->groupLevel[$level])) {
            return $this->groupLevel[$level];
        }
        trigger_error("Group '$level' does not exist.", E_USER_NOTICE);
    }

}
