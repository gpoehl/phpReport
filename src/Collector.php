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
     * Add an item to this collector.
     * @param AbstactCollector|AbstractCalulator The item to be added
     * @param int|string|null The unique item key. When the key is empty the item
     * will be added at the end of the $items array. When the key is not empty
     * it must be unique. 
     * @throws \InvalidArgumentException when the key already exists.
     */
    public function addItem($item, $key = null): void {
        if ($key === null) {
            $this->items[] = $item;
        } else {
            if (isset($this->items[$key]) || isset($this->altKeys[$key])) {
                throw new \InvalidArgumentException("Key '$key' already exists.");
            }
            $this->items[$key] = $item;
        }
    }

}
