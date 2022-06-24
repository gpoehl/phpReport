<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport\Calculator;

/**
 * Base calculator class.
 */
abstract class AbstractCalculator
{

    public readonly int $maxLevel;
    protected readonly \Closure $getLevel;
    protected $total = []; // Array which keeps cumulated values per level

    /**
     * Initialize internal properties.
     * Method is separated from constructor to allow automatic initializion
     * by an application.
     * @param \Closure $getLevel Function which accepts int|string|null value
     * and returns an integer to be used as $level.
     * @param int $maxLevel The total number of levels.
     */
    public function initialize(\Closure $getLevel, int $maxLevel) {
        $this->getLevel = $getLevel;
        $this->maxLevel = $maxLevel;
    }

    /**
     * Initialize the calculator on the current level with the given value.
     * Call this method (usually within group headers) to set an other value than
     * zero.
     * Counters, min or max values will not be influenced by calling this method.
     * @param $value The inital value.
     */
    public function setInitialValue(int|float|string $value): void {
        $this->total[($this->getLevel)()] = $value;
    }

     /**
     * Cumulate values to next higher level and reset values on given level.
     */
    abstract public function cumulateToNextLevel(int $level): void;

    abstract public function add(int|float|string|null $value): void;

    abstract public function sub(int|float|string|null $value): void;

    abstract public function sum(int $level = null);
}
