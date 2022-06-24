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

use gpoehl\phpReport\Calculator\AbstractCalculator;

/**
 * FixedSheet is a specialized collector to handle key => value pairs in multiple calculators.
 * Each key will be represented by an own calculator which can accessed by the key.
 * All calculators will be instantiated immediately when this class is instantiated.
 * Calculators are clones of the calculator given at instantiation.
 */
class FixedSheet extends AbstractCollector
{

    /**
     * @param AbstractCaclculator $calculator An instance of a calculator class.
     * @param $fromKey The first key (name) of sheet columns or iterable list of all possible keys.
     * @param $toKey The last key (name) of sheet columns. When keys are strings
     * make sure that incrementing $key will meet $toKey. Null when $fromKey is iterable.
     */
    public function __construct(AbstractCalculator $calculator, int|string|iterable $fromKey, int|string|null $toKey) {
        if (method_exists($calculator, 'getScale')) {
            $this->setScale($calculator->getScale());
        }
        if (is_iterable($fromKey)) {
            $this->addArrayItems($calculator, $fromKey);
        } else {
            $this->addItems($calculator, $fromKey, $toKey);
        }
    }

    // Implementation of arrayAccess interface. Don't allow creating new items
    public function offsetSet($offset, $value): void {
        throw new \Exception("To create new item $offset the add method must be called");
    }

    /**
     * Add items by range fromKey to toKey by cloning the calculator.
     * @param @see __construct
     */
    private function addItems(AbstractCalculator $calculator, $fromKey, $toKey) {
        $this->items[$fromKey] = $calculator;
        for ($i = $fromKey++; $i <= $toKey; $i++) {
            $this->items[$i] = clone $calculator;
        }
    }

    /**
     * Add items by given keys.
     * Clone given calculator for each key.
     * @param @see __construct
     */
    private function addArrayItems(AbstractCalculator $calculator, $keys) {
        foreach ($keys as $key) {
            $this->items[$key] = clone $calculator;
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
    public function add(iterable $values): void {
        foreach ($values as $key => $value) {
            if (!isset($this->items[$key])) {
                throw new \OutOfBoundsException("Key $key is not part of fixed sheet");
            }
            $this->items[$key]->add($value);
        }
    }

}
