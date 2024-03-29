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

use gpoehl\phpReport\Calculator\AbstractCalculator;

/**
 * Collector to hold Collectors and / or Calulators
 */
class Collector extends AbstractCollector
{

    /**
     * Add an item to this collector.
     * @param $item The item to be added
     * @param $key The unique item key. When the key is empty the item
     * will be added at the end of the $items array. When the key is not empty
     * it must be unique.
     * @param $altKey Alternate key to access an collector item. Both $key and $altKey must be unique.
     * @throws InvalidArgumentException when the key already exists.
     */
    public function addItem(AbstractCollector|AbstractCalculator $item, int|string|null $key = null, int|string|null $altKey = null): void {
        if ($key === null) {
            $this->items[] = $item;
            $key = array_key_last($this->items);
        } else {
            if (isset($this->items[$key]) || isset($this->altKeys[$key])) {
                throw new \InvalidArgumentException("Key '$key' already exists.");
            }
            $this->items[$key] = $item;
        }
        if ($altKey !== null){
            $this->setAltKey($altKey, $key);
        }
    }

}
