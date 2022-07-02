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
    /* @var $total[] Cumulatd values indexed by level */
    protected $total = [];

    /**
     * Initialize properties.
     * Must be called once. Call will usally invoked by an application (e.g. phpreport).
     * @param \Closure $getLevel Function which must accept int|string|null value
     * and return an integer representation of $level.
     * @param int $maxLevel Last level managed by this calculator.
     */
    public function initialize(\Closure $getLevel, int $maxLevel):void {
        $this->getLevel = $getLevel;
        $this->maxLevel = $maxLevel;
    }

    /**
     * Set an initial value on the **current level**.
     * Call this method (usually within group headers) to replace the current
     * value with the opening balance.
     * Calling this method has no impact on counters, min or max values.
     * @param $value The value to be set as initial value.
     */
    abstract public function setInitialValue(int|float|string $value): void;

    /**
     * Cumulate values to next higher level and reset values on given level.
     */
    abstract public function cumulateToNextLevel(int $level): void;

    /**
     * Add given $value.
     * @param $value The data value to be added
     */
    abstract public function add(int|float|string|null $value): void;

    /**
     * Subtract given $value.
     * @param $value The data value to be subtracted
     */
    abstract public function sub(int|float|string|null $value): void;

    /**
     * Get the sum of calculated values.
     * @param $level The requested level. Defaults to the current level.
     * When level is higher then $maxLevel 0 will be returned without any notice.
     * @return  The running total of calculated values at given level.
     */
    abstract public function sum(int|string|null $level = null) : int|float|string;
}
