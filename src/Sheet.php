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
 * Sheet is a specialized collector to handle key => value pairs in multiple calculators.
 * Each key will be represented by an own calculator which can accessed by the key.
 * Calculators will be instantiated during add() method and are clones of the
 * calculator given at instantiation.
 */
class Sheet extends AbstractCollector
{


    /**
     * @param AbstractCalculator $calculator Calculator used for each sheet item
     */
    public function __construct(private AbstractCalculator $calculator) {
        if (method_exists($calculator, 'getScale')){
             $this->setScale($calculator->getScale());
        }
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
     * @param iterable $values indexed by the sheet item to be added.
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
