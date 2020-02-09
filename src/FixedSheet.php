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

use Exception;
use OutOfBoundsException;

/**
 * Sheet holds values of a field into one of many cumulators.
 * The cumulator will be selected by a key.
 * Cumulators will be instantiated immediately when this class is instantiated.
 * Cumaltors are clones of class instantiation $cumulator parameter.  
 */
class FixedSheet extends AbstractCollector {

    /**
     * 
     * @param AbstractCaclculator $calculator An instance of a cualculator class which
     * will be cloned and then assigned to this object to hold values into 
     * sheet columns.
     * 
     * @param string|int $fromKey The first key (name) of sheet columns. 
     * @param string|int $toKey The last key (name) of sheet columns. When using
     * strings as keys make sure that incrementing $key will meet $toKey.
     * @param mixed $name Optional name to identify this object.
     */
    public function __construct(AbstractCalculator $calculator, $fromKey, $toKey) {
        (is_array($fromKey)) ? $this->addArrayItems($calculator, $fromKey) : $this->addItems($calculator, $fromKey, $toKey);
    }

    // Implementation of arrayAccess interface. Don't allow creating new items
    public function offsetSet($offset, $value) {
        throw new Exception("To create new item $offset the add method must be called");
    }

    /**
     * Returns true when assigned cumulater handles notNull and notZero counters.  
     * @return boolean
     */
    public function hasCounter(): bool {
        return reset($this->items)->hasCounter();
    }

    /**
     * Returns true when assigned cumulator handles min and max values. 
     * @return boolean
     */
    public function hasMinMax(): bool {
        return reset($this->items)->hasMinMax();
    }

    /**
     * Clone given cumulator from $fromKey to $toKey to have a fixed size sheet.
     * @param @see __construct
     */
    private function addItems($cumulator, $fromKey, $toKey) {
        $this->items[$fromKey] = $cumulator;
        for ($i = $fromKey++; $i <= $toKey; $i++) {
            $this->items[$i] = clone $cumulator;
        }
    }

    /**
     * Clone given calculator from $fromKey to $toKey building a fixed size sheet.
     * @param @see __construct
     */
    private function addArrayItems($cumulator, $fromKey) {
        foreach ($fromKey as $key) {
            $this->items[$key] = clone $cumulator;
        }
    }

    /**
     * Add values to fixed sheet item.
     * Values will be added to sheet items by
     * calling the add method of calculator class.
     * The calculator must already exist.
     * @param iterable $values The iterator key represents the sheet item
     * while the value will to be added. 
     */
     public function add(iterable $values) {
         foreach ($values as $key => $value) {
            if (!isset($this->items[$key])) {
                throw new OutOfBoundsException("Key $key is not part of fixed sheet");
            }
            $this->items[$key]->add($value);
         }
    }
 
}
