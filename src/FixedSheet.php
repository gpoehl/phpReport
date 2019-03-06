<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

use Exception;
use OutOfBoundsException;

/**
 * Sheet holds values of a field into one of many cumulators.
 * The cumulator will be selected by a key.
 * Cumulators will be instantiated immediately when this class is instantiated.
 * Cumaltors are clones of class instantiation $cumulator parameter.  
 *
 * @author Günter
 */
class FixedSheet extends AbstractCollector {

    /**
     * 
     * @param AbstractCumulator $cumulator An instance of a cumulator class which
     * will be cloned and then assigned to this object to hold values into 
     * sheet columns.
     * 
     * @param string|int $fromKey The first key (name) of sheet columns. 
     * @param string|int $toKey The last key (name) of sheet columns. When using
     * strings as keys make sure that incrementing $key will meet $toKey.
     * @param mixed $name Optional name to identify this object.
     */
    public function __construct(AbstractCumulator $cumulator, $fromKey, $toKey) {
        $this->addItems($cumulator, $fromKey, $toKey);
    }

    // Implementation of arrayAccess interface. Don't allow creating new items
    public function offsetSet($offset, $value) {
        throw new Exception("To create new item $offset the add method must be called");
    }

    /**
     * Clone given cumulator from $fromKey to $toKey to have a fixed size sheet.
     * @param @see __construct
     */
    private function addItems($cumulator, $fromKey, $toKey) {
        $this->items[$fromKey] = $cumulator;
        for ($i = $fromKey ++; $i <= $toKey; $i ++) {
            $this->items[$i] = clone $cumulator;
        }
    }

    /**
     * Add value to sheet item
     * Value will be added to sheet item of $key by
     * calling the add method of cumulator class.
     * The cumulator must already exist.
     * @param int|float|null $value The value to be added
     * @param string|int $key The key of the sheet cumulator (column).
     */
    public function add($value, $key) {
        if (!isset($this->items[$key])) {
            throw new OutOfBoundsException("Key $key is not part of fixed sheet");
        }
        $this->items[$key]->add($value);
    }

}
