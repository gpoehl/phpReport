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
     * Initialize all levels with 0 values
     */
    public function __construct(MajorProperties $mp, int $maxLevel) {
        parent::__construct($mp, $maxLevel);
        $this->total[$maxLevel] = 0;
    }

    /**
     * Returns always false. Counters for notNull and notZero values are not implemented. 
     * @return boolean
     */
    public function hasCounter(): bool {
        return false;
    }

    /**
     * Returns always false. Methods to handle min and max values are not implemented. 
     * @return boolean
     */
    public function hasMinMax(): bool {
        return false;
    }

    /**
     * Add value
     * The value is added to the lowest level of this calculator ($maxLevel).
     * @param numeric $value The value to be added
     * @return void
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
     * @param int|string|null $level The requested level. @see MajorProperties->getLevel()
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum($level = null) {
        $sum = 0;
        for ($i = ($this->mp->getLevel($level)); $i <= $this->maxLevel; $i++) {
            if (isset($this->total[$i])) {
                $sum += $this->total[$i];
            }
        }
        return $sum;
    }

}
