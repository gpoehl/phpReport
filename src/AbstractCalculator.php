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
abstract class AbstractCalculator {

    protected $total = []; // Array which keeps cumulated values per level

    /**
     * @param MajorPropertiesService $mp Object of major properties  
     * @param int $maxLevel The maximum (group) level 
     * @param mixed|null $objID Optioal referece of this object.
     */
    public function __construct(protected MajorProperties $mp, public int $maxLevel) {
    }

    /**
     * Initialiizes the calculator with the given value.
     * The notNull and notZero counters will not be incremented. The initial
     * value has also no influence on min and max values.
     * Note: Without initializing the initial value will always be zero. 
     * @param numeric $value The inital value.
     * @param mixed $level The level on which the value will be cumulated. This 
     * is usually the lowest level of the current dimension.
     * @return void
     * @throws \OutOfBoundsException
     */
    public function setInitialValue($value, $level = null): void {
        $level = ($level) ?? $this->mp->level;
        if ($level > $this->maxLevel) {
            throw new \OutOfBoundsException("Level $level must above maxLevel ($this->maxLevel)");
        }
        $this->initializeValue($value, $level);
    }

    abstract protected function initializeValue($value, int $level): void;

    /**
     * Cumulates values and counters to the next higher level 
     */
    abstract public function cumulateToNextLevel(): void;

    abstract public function add($value): void;

    abstract public function sum(int $level = null);
}
