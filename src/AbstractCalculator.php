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

namespace gpoehl\phpReport;

/**
 * Base class for calculator classes.
 */
abstract class AbstractCalculator
{

    protected $total = []; // Array which keeps cumulated values per level

    /**
     * @param $rep The report object where this calculator belongs to
     * @param $maxLevel The maximum (group) level
     */
    public function __construct(protected Report $rep, public int $maxLevel) {
        $this->initialize();
    }

    /**
     * Initialize the calculator on the current level with the given value.
     * Call this method (usually within group headers) to set an other value than
     * zero.
     * Counters, min or max values will not be influenced by calling this method.
     * @param $value The inital value.
     */
    public function setInitialValue(int|float|string $value): void {
        $this->total[$this->rep->currentLevel] = $value;
    }

    /**
     * Cumulates values and counters to the next higher level
     */
    abstract public function cumulateToNextLevel(int $level): void;

    abstract public function add(int|float|string|null $value): void;

    abstract public function sum(int $level = null);
}
