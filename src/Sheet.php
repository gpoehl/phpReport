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
 * Sheet holds values of a field into one of many cumulators.
 * The cumulator will be selected by a key.
 * Cumulators will be instantiated during add() method and are clones
 * of class instantiation $cumulator parameter.  
 */
class Sheet extends AbstractCollector {

    private $cumulator; // Cumulator used to create new column on demand

    public function __construct(AbstractCumulator $cumulator) {
        $this->cumulator = $cumulator;
    }

    /**
     * Returns true when assigned cumulater handles notNull and notZero counters.  
     * @return boolean
     */
    public function hasCounter(): bool {
        return $this->cumulator->hasCounter();
    }

    /**
     * Returns true when assigned cumulator handles min and max values. 
     * @return boolean
     */
    public function hasMinMax(): bool {
        return $this->cumulator->hasMinMax();
    }

    private function addItem($key = null) {
        $item = clone $this->cumulator;
        if ($key === null) {
            $this->items[] = $item;
        } else {
            $this->items[$key] = $item;
        }
    }

    /**
     * Add value to sheet item
     * Value will be added to sheet item of $key by
     * calling the add method of cumulator class.
     * The cumulator will be instantiated when $key does not already exists.
     * @param string|int $key The key of the sheet cumulator (column).
     * @param int|float|null $value The value to be added
     */
    public function add($key, $value) {
        if (!isset($this->items[$key])) {
            $this->addItem($key);
        }
        $this->items[$key]->add($value);
    }

}
