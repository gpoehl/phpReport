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
 * Base class of Collector and sheet classes.
 * Class is declared as abstract to avoid instantiation. It has no abstract
 * methods.
 */
abstract class AbstractCollector implements \ArrayAccess {

    public $items = [];     // Array holding assigned items
    private $mapper = [];   // See setMapper method

    /**
     * Mapper allows mapping of string keys to int keys to access items by
     * string key.
     * @param array $mapper Key must be a string, value the int key of $items
     */
    public function setMapper(array $mapper) {
        $this->mapper = $mapper;
    }

    /**
     * Allow direct access to item via $collector->itemKey 
     * @param mixed $key the item key 
     * @return mixed Returns the requested item
     */
    public function __get($key) {
        if (is_string($key) && isset($this->mapper[$key])) {
            $key = $this->mapper[$key];
        }
        if (isset($this->items[$key])) {
            return ($this->items[$key]);
        }
        trigger_error("Item $key does not exit", E_USER_NOTICE);
    }

    // implementation of arrayAccess interface
    public function offsetSet($offset, $value) {
        $this->addItem($value, $offset);
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) {
        trigger_error("Unset of collector item $offset is not supported.", E_USER_NOTICE);
    }

    public function offsetGet($offset) {
        if (is_string($offset) && isset($this->mapper[$offset])) {
            $offset = $this->mapper[$offset];
        }
        if (isset($this->items[$offset])) {
            return ($this->items[$offset]);
        }
        trigger_error("Item $offset does not exit", E_USER_NOTICE);
    }

    // End of arrayAccessImplementation

    public function getItems() {
        return $this->items;
    }

    public function getItem($key) {
        if (isset($this->items[$key])) {
            return ($this->items[$key]);
        }
        trigger_error("Item $key does not exit", E_USER_NOTICE);
    }
    
   /**
    * Adds values to related calculators or to other collectors. 
    * @param iterable $values Key and value pairs. Key represents the collector
    * item. Value might be numeric value or array having key => value pair(s). 
    */ 
    public function add(iterable $values) {
        foreach ($values as $item => $value){ 
            $this->items[$item]->add($value);
        }
    }

    public function cumulateToNextLevel() {
        foreach ($this->items as $item) {
            $item->cumulateToNextLevel();
        }
    }

    public function sum($level = null, bool $asArray = false) {
        $result = $this->total('sum', $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function nn($level = null, bool $asArray = false) {
        $result = $this->total('nn', $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function nz($level = null, bool $asArray = false) {
        $result = $this->total('nz', $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function min($level = null, bool $asArray = false) {
        $result = $this->total('min', $level);
        if ($asArray) {
            return $result;
        }
        // Remove null values before calling min();
        $wrk = array_filter($result);
        return (empty($wrk)) ? null : min($wrk);
    }

    public function max($level = null, bool $asArray = false) {
        $result = $this->total('max', $level);
        return ($asArray) ? $result : (empty($result) ? null : max($result));
    }

    protected function total(string $typ, $level = null): array {
        $sum = [];
        foreach ($this->items as $key => $item) {
            $sum[$key] = $item->$typ($level);
        }
        return $sum;
    }

    public function rsum($fromKey, $toKey = null, $level = null, bool $asArray = false) {
        $result = $this->rtotal('sum', $fromKey, $toKey, $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function rnz($fromKey, $toKey = null, $level = null, bool $asArray = false) {
        $result = $this->rtotal('nz', $fromKey, $toKey, $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function rnn($fromKey, $toKey = null, $level = null, bool $asArray = false) {
        $result = $this->rtotal('nn', $fromKey, $toKey, $level);
        return ($asArray) ? $result : array_sum($result);
    }

    public function rmin($fromKey, $toKey = null, $level = null, bool $asArray = false) {
        $result = $this->rtotal('min', $fromKey, $toKey, $level);
        return ($asArray) ? $result : min($result);
    }

    public function rmax($fromKey, $toKey = null, $level = null, bool $asArray = false) {
        $result = $this->rtotal('max', $fromKey, $toKey, $level);
        return ($asArray) ? $result : max($result);
    }

    protected function rtotal(string $typ, $fromKey, $toKey = null, $level = null): array {
        $result = [];
        $toKey = ($toKey) ?? $fromKey;
        if (isset($this->items)) {
            for ($i = $fromKey; $i <= $toKey; $i++) {
                if (isset($this->items[$i])) {
                    $result[$i] = $this->items[$i]->$typ($level);
                } else {
                    $result[$i] = null;
                }
            }
        }
        return $result;
    }

}
