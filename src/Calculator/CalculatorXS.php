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

    public function initialize(\Closure $getLevel, int $maxLevel):void {
        parent::initialize($getLevel, $maxLevel);
        $this->total = array_fill(0, $this->maxLevel + 1, 0);
    }

     public function setInitialValue(int|float|string $value): void {
        $this->total[($this->getLevel)()] = $value;
    }


    public function add(int|float|string|null $value): void {
        $this->total[$this->maxLevel] += $value;
    }

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

    public function cumulateToNextLevel(int $level): void {
        if ($level <= $this->maxLevel) {
            $this->total[$level - 1] += $this->total[$level];
            $this->total[$level] = 0;
        }
    }

    public function sum(int|string|null $level = null) :int|float {
        return array_sum(array_slice($this->total, ($this->getLevel)($level)));
    }

}
