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

/*
 * Handle minimum and maximum values
 */

Trait MinMaxTrait
{

    /** @var mixed[] Minimum value indexed by level */
    private $min = [];

    /** @var mixed[] Maximum value indexed by level */
    private $max = [];

    private function initializeMinMax($level) {
        $this->min = $this->max = array_fill(0, $level + 1, null);
    }

    /**
     * Set min and max values.
     * Both are set when value is not null.
     * @param $value The data value to be compared
     */
    private function setMinMax(int|float|string|null $value): void {
        if ($value === null) {
            return;
        }
        if ($this->max[$this->maxLevel] === null) {
            // When maxLevel is null then minLevel is also null. Both must be set.
            $this->min[$this->maxLevel] = $this->max[$this->maxLevel] = $value;
        } elseif ($value > $this->max[$this->maxLevel]) {
            $this->max[$this->maxLevel] = $value;
            // When value is greater max value it can't be less min.
        } elseIf ($value < $this->min[$this->maxLevel]) {
            $this->min[$this->maxLevel] = $value;
        }
    }

    private function cumulateMinMaxToNextLevel(int $level): void {
        $next = $level - 1;
        If ($this->min[$level] < $this->min[$next] || $this->min[$next] === null) {
            $this->min[$next] = $this->min[$level];
        }
        if ($this->max[$level] > $this->max[$next] || $this->max[$next] === null) {
            $this->max[$next] = $this->max[$level];
        }
        $this->min[$level] = $this->max[$level] = null;
    }

    /**
     * Get the minimum non null value.
     * When no rows are processed or all values had been null the returned
     * value will be null.
     * @param $level The requested level. Defaults to the current level
     * @return null|mixed The lowest value within the given level
     */
    public function min(int|string|null $level = null) {
        $wrk = array_filter(array_slice($this->min, ($this->getLevel)($level)), fn($val)=>($val !==null));
        return (empty($wrk)) ? null : min($wrk);
    }

    /**
     * Get the maximum value.
     * When no rows are processed or all values have been null the returned value
     * will also be null.
     * @param $level The requested group level. Defaults to the current level.
     * @return null|mixed The maximum value within the given level
     */
    public function max(int|string|null $level = null) {
        $wrk = array_filter(array_slice($this->max, ($this->getLevel)($level)), fn($val)=>($val !==null));
        return (empty($wrk)) ? null : max($wrk);
    }

}
