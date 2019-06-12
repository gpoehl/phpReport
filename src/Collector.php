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
 * Collector holds cumulator and sheet items
 */
class Collector extends AbstractCollector {

    /**
     * @inheritdoc
     */
    public function __set($key, $value) {
        $this->addItem($value, $key);
    }

    /**
     * @inheritdoc
     */
    public function addItem($item, $key = null) {
        if ($key != null) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function addItems($item, $fromKey = 0, $toKey = null) {
        if (is_array($fromkey)) {
            foreach ($fromKey as $key) {
                $this->addItem(clone $item, $key);
            }
        } else {
            for ($key = $fromKey; $key <= $toKey; $key ++) {
                $this->addItem(clone $item, $key);
            }
        }
    }

}
