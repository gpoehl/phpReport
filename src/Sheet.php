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
class Sheet extends AbstractCollector
{

    /**
     * @param AbstractCalculator $calculator Calculator used to create new column on demand
     */
    public function __construct(private AbstractCalculator $calculator) {

    }

    private function addItem($key) {
        $item = clone $this->calculator;
        $this->items[$key] = $item;
    }

    /**
     * Add values to items
     * Values will be added to sheet items by
     * calling the add method of calculator class.
     * The calculator will be instantiated when he doesn't already exists.
     * @param iterable $values The iterator key represents the sheet item
     * while the value will to be added.
     */
    public function add(iterable $values): void {
        foreach ($values as $key => $value) {
            if (!isset($this->items[$key])) {
                $this->addItem($key);
            }
            $this->items[$key]->add($value);
        }
    }

}
