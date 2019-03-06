<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Sheet holds values of a field into one of many cumulators.
 * The cumulator will be selected by a key.
 * Cumulators will be instantiated during add() method and are clones
 * of class instantiation $cumulator parameter.  
 *
 * @author Günter
 */
class Sheet extends AbstractCollector {

    private $cumulator; // Cumulator used to create new column on demand

    public function __construct(AbstractCumulator $cumulator) {
        $this->cumulator = $cumulator;
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
    public function add($value, $key) {
        if (!isset($this->items[$key])) {
            $this->addItem($key);
        }
        $this->items[$key]->add($value);
    }

}
