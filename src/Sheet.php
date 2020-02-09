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

    private $calculator; // calculator used to create new column on demand

    public function __construct(AbstractCalculator $calculator) {
        $this->calculator = $calculator;
    }

    /**
     * Returns true when assigned calculator handles notNull and notZero counters.  
     * @return boolean
     */
    public function hasCounter(): bool {
        return $this->calculator->hasCounter();
    }

    /**
     * Returns true when assigned calculator handles min and max values. 
     * @return boolean
     */
    public function hasMinMax(): bool {
        return $this->calculator->hasMinMax();
    }

    private function addItem($key = null) {
        $item = clone $this->calculator;
        if ($key === null) {
            $this->items[] = $item;
        } else {
            $this->items[$key] = $item;
        }
    }

    /**
     * Add values to sheet item
     * Values will be added to sheet items by
     * calling the add method of calculator class.
     * The calculator will be instantiated when when he doesn't already exists.
    * @param iterable $values The iterator key represents the sheet item
     * while the value will to be added. 
     */
     public function add(iterable $values) {
        foreach ($values as $key => $value) {
            if (!isset($this->items[$key])) {
                $this->addItem($key);
            }
            $this->items[$key]->add($value);
        }
    }

}
