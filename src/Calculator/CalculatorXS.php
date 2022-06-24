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

namespace gpoehl\phpReport\Calculator;

/**
 * Calculator with minimum functionality
 * Add, sub or increment value on the last level ($maxlevel).
 * Results are saved at $maxLevel levels to provide totals and subtotals.
 * @see Calculator or CalculatorXL classes for enhanced functionality.
 */
class CalculatorXS extends AbstractCalculator
{

    /**
     * Initialize all levels with 0 values
     * Don't call this method yourself. The report class takes care for calling.
     */
    public function initialize(\Closure $getLevel, int $maxLevel) {
        parent::initialize($getLevel, $maxLevel);
        $this->total = array_fill(0, $this->maxLevel + 1, 0);
    }

    /**
     * Add given $value to $maxLevel
     * @param numeric|null $value The value to be added
     */
    public function add(int|float|string|null $value): void {
        $this->total[$this->maxLevel] += $value;
    }

    /**
     * Subtract given $value from $maxLevel
     * @param numeric|null $value The value to be subtracted
     */
    public function sub(int|float|string|null $value): void {
        $this->total[$this->maxLevel] -= $value;
    }

     /**
     * Increment value at the lowest level of this calculator ($maxLevel).
     * This is a shortcut of add(1) and best used for counters.
     */
    public function inc(): void {
        $this->total[$this->maxLevel]++;
    }

    /**
     * Don't call this method yourself. The report class takes care for calling.
     * Cumulate attribute values to higher level.
     */
    public function cumulateToNextLevel(int $level): void {
        if ($level <= $this->maxLevel) {
            $this->total[$level - 1] += $this->total[$level];
            $this->total[$level] = 0;
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|string|null $level The requested level. @see MajorProperties->getLevel()
     * @return int|float The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum(int|string|null $level = null) {
        return array_sum(array_slice($this->total, ($this->getLevel)($level)));
    }

}
