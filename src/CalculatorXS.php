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
 * Calculator to summarize or increment an attribute
 * This calculator offers only a minimum functionality while using minimum ressources.
 * Use class Calculator or CulculatorXL if you want additional counters or methods.
 */
class CalculatorXS extends AbstractCalculator {

    /**
     * @param MajorPropertiesService $mp Object of major properties  
     * @param int $maxLevel The maximum (group) level 
     * Initialize all levels with 0 values
     */
    public function __construct(protected MajorProperties $mp, public int $maxLevel) {
        $this->total = array_fill(0, $maxLevel + 1, 0);
    }

    /**
     * Returns always false. Counters for notNull and notZero values are not implemented. 
     * @return false
     */
    public function hasCounter(): bool {
        return false;
    }

    /**
     * Returns always false. Methods to handle min and max values are not implemented. 
     * @return false
     */
    public function hasMinMax(): bool {
        return false;
    }

    /**
     * Add given $value to $maxLevel
     * @param numeric|null $value The value to be added
     */
    public function add($value): void {
        $this->total[$this->maxLevel] += $value;
    }

    protected function initializeValue($value, int $level): void {
        $this->total[$level] = $value;
    }

    /**
     * Increment value
     * The value is incremented on the lowest level of this calculator ($maxLevel).
     * This is a shortcut of add(1) and best used for counters.
     * @return void
     */
    public function inc(): void {
        $this->total[$this->maxLevel]++;
    }

     /**
     * Cumulate attribute values to higher level.
     * Add values from the current level to the next higher level (which is 1 less
     * then the current level). Values on current level will be reset to zero. 
     */
    public function cumulateToNextLevel(): void {
        $level = $this->mp->level;
        if ($level <= $this->maxLevel) {
            $this->total[$level - 1] += $this->total[$level];
            $this->total[$level] = 0;
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|string|null $level The requested level. @see MajorProperties->getLevel()
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum($level = null) {
        return array_sum(array_slice($this->total, $this->mp->getLevel($level)));
    }

}
