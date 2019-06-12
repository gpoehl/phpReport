<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Base class for cumulator classes.
 */
abstract class AbstractCumulator {

    protected $mp;         // Major properties object
    public $maxLevel;      // The maximum (group) level
    protected $total = []; // Array which keeps cumulated values per level

    /**
     * @param MajorPropertiesService $mp Object of major properties  
     * @param int $maxLevel The maximum (group) level 
     * @param mixed|null $objID Optioal referece of this object.
     */
    public function __construct(MajorProperties $mp, int $maxLevel) {
        $this->mp = $mp;
        $this->maxLevel = $maxLevel;
    }

    /**
     * Cumulate values for given key (bucket or range) and increment counters.
     * @param mixed $key The bucket or range key to which the value belongs
     * @param numeric $value The numeric data value which will be cumulated
     * @param int $level The level on which the value will be cumulated. This 
     * is usually the lowest level of the dim which has the value.
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

    abstract public function cumulateToNextLevel(): void;

    abstract public function add($value): void;

    abstract public function sum(int $level = null);
}
