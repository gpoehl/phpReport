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

use ArrayAccess;
use InvalidArgumentException;

/**
 * Base class of Collector and sheet classes.
 * Class is declared as abstract to avoid instantiation. It has no abstract
 * methods.
 */
abstract class AbstractCollector implements ArrayAccess {

    public $items = [];     // Array holding assigned items
    private $mapper = [];   // See setMapper method

    /**
     * Mapper allows mapping of string keys to int keys to access items by
     * string key.
     * This method is also used to allow access to group counter items by name or level.
     * Note: Range and between methods don't use the mapper. 
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
        foreach ($values as $item => $value) {
            $this->items[$item]->add($value);
        }
    }

    public function cumulateToNextLevel() {
        foreach ($this->items as $item) {
            $item->cumulateToNextLevel();
        }
    }

    /**
     * Reduce items by ranges.
     * A range can be an array with start and end item or a single item key.
     * @param array|int|string $ranges Any number of ranges can be passed. When 
     * a range is not an array than the single item will be selected. Not existing
     * single items are ignored.
     * For arrays the first element is the start item and the second element
     * the last item to be selected.
     * When the first item is null items from the beginning are selected. 
     * When the second item is null all items following the first item are selected.
     * The start item and the last item must exist. 
     * @return AbstractCollector Collector collecting only selected itemes
     * @throws InvalidArgumentException When start or end item doesn't exist.
     */
    public function range(... $ranges): AbstractCollector {
        $result = [];
        $singleSelects = [];
        $itemKeys = array_keys($this->items);
        foreach ($ranges as $range) {
            if (is_array($range)) {
                if ($range[0] === null) {
                    $offset = 0;
                } else {
                    $offset = array_search($range[0], $itemKeys, true);
                    if ($offset === false) {
                        throw new InvalidArgumentException("From key $range[0] doesn't exist.");
                    }
                }
                if (isset($range[1])) {
                    $toKey = array_search($range[1], $itemKeys, true);
                    if ($toKey === false) {
                        throw new InvalidArgumentException("To key $toKey doesn't exist.");
                    }
                    $length = $toKey - $offset + 1;
                } else {
                    $length = null;
                }
                $result = $result + array_slice($this->items, $offset, $length, true);
            } else {
                $singleSelects [] = $range;
            }
        }
        return $this->getReducedClone($result, $singleSelects);
    }

    /**
     * Get collector with items where key values matches any of given ranges.
     * A range can be an array having lowest and highest key values or single
     * key values.
     * Condition will return items where key values are within the range of value1 and value2 (inclusive).
     * A range is usually an array with start and end item or a single item key.
     * @param array|int|string $ranges Any number of ranges can be passed. When 
     * a range is not an array than the single item will be selected. Not existing
     * single items are ignored.
     * For arrays the first element is the start item and the second element
     * the last item to be selected.
     * When the first item is null items from the beginning are selected. 
     * When the second item is null all items following the first item are selected.
     * The start item and the last item must exist. 
     * @return AbstractCollector Collector collecting only selected itemes
     * @throws InvalidArgumentException When start or end item doesn't exist.
     */
    public function between(... $ranges): AbstractCollector {
        $result = [];
        $singleSelects = [];
        $itemKeys = array_keys($this->items);
        foreach ($ranges as $range) {
            if (is_array($range)) {
                $result += $this->getBetweenItems($range);
            } else {
                $singleSelects [] = $range;
            }
        }
        return $this->getReducedClone($result, $singleSelects);
    }

    private function getBetweenItems(array $range): array {
        [$fromKey, $toKey] = $range;
        $result = [];
        foreach ($this->items as $key => $value) {
            if ($key >= $fromKey && $key <= $toKey) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    
      public function walk(callable $callable): AbstractCollector {
        $result = [];
        foreach ($this->items as $key => $value) {
            if ($callable($key, $value)) {
                $result[$key] = $value;
            }
        }
        return $this->getReducedClone($result, []);
    }

    private function getReducedClone(array $result, array $singleSelects): AbstractCollector {
        // Add single selected items to result. Missing items are ignored.
        if (!empty($singleSelects)) {
            $result += \array_intersect_key($this->items, \array_flip($singleSelects));
        }
        $ret = clone($this);
        $ret->items = $result;
        return $ret;
    }
    
    public function cmd(callable $callable): array{
        $result = $command($this->items);
        return $this->getReducedClone((is_array($result)? $result: $this->items), []);
    }

    /* -------------------------------------------------------------------------
     * Aggregate functions
     * ---------------------------------------------------------------------- */

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

}
