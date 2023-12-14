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
 * Summarize values, provide notNull and notZero counters and averages.
 */
class Calculator extends CalculatorXS
{

    use CounterTrait;

    public function initialize(\Closure $getLevel, int $maxLevel): void {
        parent::initialize($getLevel, $maxLevel);
        $this->initializeCounter();
    }

    public function add(int|float|string|null $value): void {
        $this->counter[$this->maxLevel]++;
        if ($value !== null) {
            $this->nnCounter[$this->maxLevel]++;
            if ($value != 0) {
                $this->nzCounter[$this->maxLevel]++;
                $this->total[$this->maxLevel] += $value;
            }
        }
    }

    public function sub(int|float|string|null $value): void {
        $this->counter[$this->maxLevel]++;
        if ($value !== null) {
            $this->nnCounter[$this->maxLevel]++;
            if ($value != 0) {
                $this->nzCounter[$this->maxLevel]++;
                $this->total[$this->maxLevel] -= $value;
            }
        }
    }

    public function cumulateToNextLevel(int $level): void {
            $next = $level - 1;
            $this->total[$next] += $this->total[$level];
            $this->counter[$next] += $this->counter[$level];
            $this->nnCounter[$next] += $this->nnCounter[$level];
            $this->nzCounter[$next] += $this->nzCounter[$level];
            $this->total[$level] = $this->counter[$level] = $this->nnCounter[$level] = $this->nzCounter[$level] = 0;
    }

    protected function getAvg(int|string|null $level, array $counter): int|float|string|null {
        $level = ($this->getLevel)($level);
        $divisor = array_sum(array_slice($counter, $level));
        return ($divisor == 0) ? null :
                array_sum(array_slice($this->total, $level)) / $divisor;
    }

}
