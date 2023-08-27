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
 * Calculator with enhanced functionality providing arbitrary precision numbers.
 * Add or sub value using bcMath methods on the last level ($maxlevel).
 * This class provides also counters and various avg methods. Counters are always
 * int types where avg methods will return results of bcdiv method.
 * Results are saved at $maxLevel levels to provide totals and subtotals.
 * @see CalculatorBcm for minimum or CalculatorBcmXL classes for enhanced functionality.
 */
class CalculatorBcm extends CalculatorBcmXS
{

    use CounterTrait;
    use PrecisionTrait;

    /**
     * @param $scale Number of digits after the decimal place for math functions.
     * Defalults to 2 (instead of 0 php defalult of null).
     * See php bcmath documentation for details.
     * Note: scale truncates the value. It does not round the value!
     */
    public function __construct(int|null $scale = 2) {
        $this->setScale($scale);
    }

    public function initialize(\Closure $getLevel, int $maxLevel): void {
        parent::initialize($getLevel, $maxLevel);
        $this->initializeCounter();
        $this->total = array_fill(0, $this->maxLevel + 1, $this->zero);
    }

    public function add(int|float|string|null $value): void {
        $this->counter[$this->maxLevel]++;
        if ($value !== null) {
            $this->nnCounter[$this->maxLevel]++;
            if ($value != 0) {
                $this->nzCounter[$this->maxLevel]++;
                $this->total[$this->maxLevel] = bcadd($this->total[$this->maxLevel], (string) $value, $this->scale);
            }
        }
    }

    public function sub(int|float|string|null $value): void {
        $this->counter[$this->maxLevel]++;
        if ($value !== null) {
            $this->nnCounter[$this->maxLevel]++;
            if ($value != 0) {
                $this->nzCounter[$this->maxLevel]++;
                $this->total[$this->maxLevel] = bcsub($this->total[$this->maxLevel], (string) $value, $this->scale);
            }
        }
    }

    public function cumulateToNextLevel(int $level): void {
            $next = $level - 1;
            $this->total[$next] = bcadd($this->total[$next], $this->total[$level], $this->scale);
            $this->counter[$next] += $this->counter[$level];
            $this->nnCounter[$next] += $this->nnCounter[$level];
            $this->nzCounter[$next] += $this->nzCounter[$level];
            $this->total[$level] = $this->zero;
            $this->counter[$level] = $this->nnCounter[$level] = $this->nzCounter[$level] = 0;
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

    protected function getAvg(int|string|null $level, array $counter): string|null {
        $level = ($this->getLevel)($level);
        $divisor = array_sum(array_slice($counter, $level));
        return ($divisor == 0) ? null :
                bcdiv($this->sum($level), (string) $divisor, $this->scale);
    }

}
