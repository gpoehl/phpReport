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
 * Collector to hold AbstactCollectors and or AbstractCalulators
 */
class Collector extends AbstractCollector {

    /**
     * Add new item by magic method.
     * Example:
     * $collector->abc = $calculator
     * Add item $calculator with key 'abc' to items array. 
     */
    public function __set($key, $value) {
        $this->addItem($value, $key);
    }

    /**
     * Add an item to this collector
     * @param AbstactCollector|AbstractCalulator The item to be added
     * @param int|string|null The item key
     */
    public function addItem($item, $key = null): void {
        if (empty($key)) {
            $this->items[] = $item;
        } else {
            if (isset($this->items[$key]) || isset($this->altKeys[$key])) {
                throw new \InvalidArgumentException("Key '$key' already exists.");
            }
            $this->items[$key] = $item;
        }
    }

}
