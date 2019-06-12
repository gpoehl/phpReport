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
 * Cumulator to summarize an attribute and count how often a not null and 
 * not zero value is given to the add() method.
 */
class Cumulator extends AbstractCumulator {

    protected $nn = [];      // Array of not null counter
    protected $nz = [];      // Array of not zero counter

    public function __construct(MajorProperties $mp, int $maxLevel) {
        parent::__construct($mp, $maxLevel);
        // Initialize arrays for cumulated values and counters only on $maxLevel
        $this->initializeValue(0, $maxLevel);
    }

    /**
     * Returns always true. Counters for notNull and notZero values are implemented.  
     * @return boolean
     */
    public function hasCounter(): bool {
        return true;
    }

    /**
     * Returns always false. Methods to handle min and max values are not implemented. 
     * @return boolean
     */
    public function hasMinMax(): bool {
        return false;
    }

    /**
     * Add given $value to $maxLevel and increment counters.
     * @param numeric $value The numeric data value which will be added
     * @return void
     */
    public function add($value): void {
        if ($value !== null) {
            $this->nn[$this->maxLevel] ++;
            if ($value !== 0) {
                $this->nz[$this->maxLevel] ++;              // increase non zero counter
                $this->total[$this->maxLevel] += $value;    // add value 
            }
        }
    }

    protected function initializeValue($value, int $level): void {
        $this->total[$level] = $value;
        $this->nz[$level] = $this->nn[$level] = 0;
    }

    /**
     * Cumulate attribute values to higher level
     */
    public function cumulateToNextLevel(): void {
        $level = $this->mp->level;
        if ($level > $this->maxLevel) {
            return;
        }
        $next = $level - 1;
        if (isset($this->total[$next])) {
            $this->total[$next] += $this->total[$level];
            $this->nn[$next] += $this->nn[$level];
            $this->nz[$next] += $this->nz[$level];
        } else {
            $this->total[$next] = $this->total[$level];
            $this->nn[$next] = $this->nn[$level];
            $this->nz[$next] = $this->nz[$level];
        }
        // Throw away current level when not maxLevel. So add() don't need isset(). 
        if ($level !== $this->maxLevel) {
            unset($this->total[$level], $this->nn[$level], $this->nz[$level]);
        } else {
            $this->initializeValue(0, $this->maxLevel);
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum($level = null) {
        return $this->runningTotal($this->total, $level);
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function nn($level = null) {
        return $this->runningTotal($this->nn, $level);
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function nz($level = null) {
        return $this->runningTotal($this->nz, $level);
    }

    private function runningTotal(array $arr, $level = null) {
        $sum = 0;
        for ($i = $this->mp->getLevel($level); $i <= $this->maxLevel; $i++) {
            if (isset($this->total[$i])) {
                $sum += $arr[$i];
            }
        }
        return $sum;
    }

}
