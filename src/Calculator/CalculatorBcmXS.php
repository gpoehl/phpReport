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
 * Calculator with minimum functionality providing arbitrary precision numbers.
 * Add or sub value using bcMath methods on the last level ($maxlevel).
 * Results are saved at $maxLevel levels to provide totals and subtotals.
 * @see CalculatorBcm or CalculatorBcmXL classes for enhanced functionality.
 */
class CalculatorBcmXS extends AbstractCalculator
{
use PrecisionTrait;
    /**
     * @param $scale Number of digits after the decimal place in the result.
     * Default to 2 (instead of 0 php defalult of null).
     * See php bcmath documentation for details.
     */
    public function __construct(int|null $scale = 2) {
         $this->setScale($scale);
    }

    public function initialize(\Closure $getLevel, int $maxLevel) :void {
        parent::initialize($getLevel, $maxLevel);
        $this->total = array_fill(0, $this->maxLevel + 1, $this->zero);
    }

    public function setInitialValue(int|float|string $value): void {
        $this->total[($this->getLevel)()] = bcadd('0', (string)$value, $this->scale);
    }

    public function add(int|float|string|null $value): void {
        $this->total[$this->maxLevel] = bcadd($this->total[$this->maxLevel], (string) $value, $this->scale);
    }

    public function sub(int|float|string|null $value): void {
        $this->total[$this->maxLevel] = bcsub($this->total[$this->maxLevel], (string) $value, $this->scale);
    }

    public function cumulateToNextLevel(int $level): void {
            $next = $level - 1;
            $this->total[$next] = bcadd($this->total[$next], $this->total[$level], $this->scale);
            $this->total[$level] = $this->zero;
    }

    public function sum(int|string|null $level = null): string {
        // All values from the current level down to the lowest level needs to be summarized
        $wrk = array_slice($this->total, ($this->getLevel)($level));
        $sum = $this->zero;
        foreach ($wrk as $value) {
            $sum = bcadd($sum, $value, $this->scale);
        }
        return $sum;
    }

}
