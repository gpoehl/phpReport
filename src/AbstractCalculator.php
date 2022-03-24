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
    protected report $rep;
    /**
     * @param MajorPropertiesService $mp Object of major properties
     * @param int $maxLevel The maximum (group) level
     * @param mixed|null $objID Optioal referece of this object.
     */
    public function __construct(protected MajorProperties $mp, public int $maxLevel) {

    }

    /**
     * Initialize the calculator on the current level with the given value.
     * Call this method in group headers to set an other value than zero.
     * @param $value The inital value.
     */
    public function setInitialValue(int|float|string $value): void {
//        if ($this->rep->currentAction->key !== 'groupHeader'){
//            throw new Exception('Initial values can only be set in group headers');
//        }
        $this->total[$this->mp->level] = $value;
    }

    /**
     * Cumulates values and counters to the next higher level
     */
    abstract public function cumulateToNextLevel(int $level): void;

    abstract public function add(int|float|string|null $value): void;

    abstract public function sum(int $level = null);
}
