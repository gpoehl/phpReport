<?php

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Cumulator to summarize or increment an attribute
 * This cumulator offers only a minimum functionality. Use class Cumulator or
 * CumulatorXL if you want additional counters or methods.
 * This is best to be used for maximum speed. 
 * @author Günter
 */
class CumulatorXS extends AbstractCumulator {

    /**
     * Initialize all levels with 0 values
     */
    public function __construct(MajorProperties $mp, int $maxLevel) {
        parent::__construct($mp, $maxLevel);
        $this->total[$maxLevel] = 0;
    }

    /**
     * Add value
     * The value is added to the lowest level of this cumulator ($maxLevel).
     * @param numeric $value The value to be added
     * @return void
     */
    public function add($value): void {
        // not test on is_numeric to improve performance
        $this->total[$this->maxLevel] += $value;
    }

    protected function initializeValue($value, int $level): void {
        $this->total[$level] = $value;
    }

    /**
     * Increment value
     * The value is incremented on the lowest level of this cumulator ($maxLevel).
     * This is a shortcut of add(1) and best used for counters.
     * @return void
     */
    public function inc(): void {
        $this->total[$this->maxLevel] ++;
    }

    public function cumulateToNextLevel(): void {
        $level = $this->mp->level;
        if ($level > $this->maxLevel) {
            return;
        }
        $next = $level - 1;
        if (isset($this->total[$next])) {
            $this->total[$next] += $this->total[$level];
        } else {
            $this->total[$next] = $this->total[$level];
        }
        // Throw away current level when not maxLevel. So add() don't need isset(). 
        if ($level !== $this->maxLevel) {
            unset($this->total[$level]);
        } else {
            $this->total[$level] = 0;
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum(int $level = null) {
        $sum = 0;
        for ($i = ($this->mp->getLevel($level)); $i <= $this->maxLevel; $i++) {
            if (isset($this->total[$i])) {
                $sum += $this->total[$i];
            }
        }
        return $sum;
    }

}
