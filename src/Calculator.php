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
 * Summarize values and count how often not null and not zero values are given
 * to the add() method.
 */
class Calculator extends AbstractCalculator implements NnAndNzCounterIF
{

    /** @var int[] not null counter. How many added values had a value not equal to zero. */
    protected $nn = [];

    /** @var int[] not zero counter. How many added values had a value not equal to zero. */
    protected $nz = [];

    /**
     * @param MajorPropertiesService $mp Object of major properties
     * @param int $maxLevel The maximum (group) level
     * Initialize all levels with 0 values
     */
    public function __construct(protected MajorProperties $mp, public int $maxLevel) {
        $this->total = $this->nz = $this->nn = array_fill(0, $maxLevel + 1, 0);
    }

    /**
     * Add given $value to $maxLevel and increment counters.
     * @param $value The data value which will be added
     */
    public function add(int|float|string|null $value): void {
        if ($value !== null) {
            $this->nn[$this->maxLevel]++;
            if ($value !== 0) {
                $this->nz[$this->maxLevel]++;              // increase non zero counter
                $this->total[$this->maxLevel] += $value;   // add value
            }
        }
    }

    /**
     * Cumulate attribute values to higher level.
     * Add values from the current level to the next higher level (which is 1 less
     * then the current level. Values on current level will be reset to zero.
     */
    public function cumulateToNextLevel(int $level): void {
        if ($level <= $this->maxLevel) {
            $next = $level - 1;
            $this->total[$next] += $this->total[$level];
            $this->nn[$next] += $this->nn[$level];
            $this->nz[$next] += $this->nz[$level];
            $this->total[$level] = $this->nn[$level] = $this->nz[$level] = 0;
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function sum(int|string|null $level = null) {
        return array_sum(array_slice($this->total, $this->mp->getLevel($level)));
    }

    /**
     * Get the number of added values beeing not null.
     * @param $level The requested level. Defaults to the current level.
     * @return The total number of added not null values for the requested level.
     */
    public function nn(int|string|null $level = null): int {
        // To calculate the total number all values from requested level down
        // to lowest level must be included.
        return array_sum(array_slice($this->nn, $this->mp->getLevel($level)));
    }

    /**
     * Get the number of added values beeing not zero and not null.
     * @param $level The requested level. Defaults to the current level.
     * @return The total number of added not null and not zero values for
     * the requested level.
     */
    public function nz(int|string|null $level = null): int {
        // To calculate the total number all values from requested level down
        // to lowest level must be included.
        return array_sum(array_slice($this->nz, $this->mp->getLevel($level)));
    }

}
