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
 * Calculator with maximum functionality.
 * Next to the parent class also min() and max() values are identified.
 * Detection and maintaining min and max values comes with the cost of reduced
 * performance. When min or max value is not needed use other Cumulator class. 
 */
class CalculatorXL extends Calculator implements MinMaxIF{

    /** @var mixed[] Minimum value per level. Key is level */
    protected $min = [];

    /** @var mixed[] Maximum value per level. Key is level */
    protected $max = [];

    /**
     * Initialize min / max with null values
     */
    public function __construct(MajorProperties $mp, int $maxLevel) {
        parent::__construct($mp, $maxLevel);
        $this->min = $this->max = array_fill(0, $maxLevel + 1, null);
    }

    /**
     * Add value to $maxLevel and set min and max values.
     * Min and max values are set when value is not null. 
     */
    public function add($value): void {
        parent::add($value);
        if ($this->max[$this->maxLevel] === null) {
            $this->min[$this->maxLevel] = $this->max[$this->maxLevel] =$value;
        } elseif ($value > $this->max[$this->maxLevel]) {
            $this->max[$this->maxLevel] = $value;
            // When value is greater max value it can't be less min.
        } elseIf ($value !== null && $value < $this->min[$this->maxLevel]) {
            $this->min[$this->maxLevel] = $value;
        }
    }

    protected function initializeValue($value, int $level): void {
        $this->total[$level] = $value;
        $this->nz[$level] = $this->nn[$level] = 0;
        $this->min[$level] = $this->max[$level] = null;
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
        $this->total[$next] += $this->total[$level];
        $this->nn[$next] += $this->nn[$level];
        $this->nz[$next] += $this->nz[$level];
        $this->total[$level] = $this->nn[$level] = $this->nz[$level] = 0;

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
     * Only when no rows are processed or all values have been null the returned
     * value will also be null.
     * If you need to know that one or more values (but not all) had been null 
     * compare the row counter with the not null counter. 
     * @param int|null $level The requested level. Defaults to the current level
     * @return null|mixed The lowest value within the given level
     */
    public function min(int $level = null) {
        // Initalize min with value of lowest level and loop from requested
        // level down to level above lowest level. This reduced the number of
        // iterations by 1. 
        $min = $this->min[$this->maxLevel];
        for ($i = $this->mp->getLevel($level); $i < $this->maxLevel; $i++) {
            if ($this->min[$i] < $min || $min === null) {
                $min = $this->min[$i];
            }
        }
        return $min;
    }

    /**
     * Get the maximum value. 
     * When no rows are processed or all values have been null the returned value
     * will also be null.
     * @param int|null $level The requested group level. Defaults to the current level.
     * @return null|mixed The maximum value within the given level
     */
    public function max(int $level = null) {
        // Same logic as for min().
        $max = $this->max[$this->maxLevel];
        for ($i = $this->mp->getLevel($level); $i < $this->maxLevel; $i++) {
            if ($this->max[$i] > $max) {
                $max = $this->max[$i];
            }
        }
        return $max;
    }

}
