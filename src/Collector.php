<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Description of Collector
 *
 * @author Günter
 */
class Collector extends AbstractCollector {

    public function __set($key, $value) {
        $this->addItem($value, $key);
    }

    public function addItem($item, $key = null) {
        if ($key != null) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }
    }

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
