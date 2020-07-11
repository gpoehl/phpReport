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

    /** @var Array of calculator or collector objects. */
    public array $items = [];

    /** @var Array of alternate keys to access items. */
    protected array $altKeys = [];

    /**
     * Set alternate keys to access items.
     * @param int|string[] $keys Item keys indexed by altenate keys.
     */
    public function setAltKeys(array $keys): void {
        foreach ($keys as $key => $itemKey) {
            $this->setAltKey($key, $itemKey);
        }
    }

    /**
     * Set alternate access key for an item.
     * @param int|string $key Unique altenate key to access an item.
     * @param int|string $itemKey The key of the item in $items.
     */
    public function setAltKey($key, $itemKey): void {
        if (isset($this->items[$key]) || isset($this->altKeys[$key])) {
            throw new InvalidArgumentException("Key '$key' already exists.");
        }
        $this->altKeys[$key] = $itemKey;
    }

    /**
     * Magic get method to allow access item via arrow notation.
     * Example: $collector->itemKey 
     * @param mixed $key the item key 
     * @return AbstractCollector|AbstractCalculator Returns the requested item
     */
    public function __get($key): object {
        return $this->getItem($key);
    }

    /* -------------------------------------------------------------------------
     * Implementation of the arrayAccess interface
     * ---------------------------------------------------------------------- */

    /*
     * Add new item to items array via array notation 
     * @param int|string $offset The item key
     * @param AbstractCollector|AbstractCalculator $value The item
     */
    public function offsetSet($offset, $value):void {
        $this->addItem($value, $offset);
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]) || isset($this->items[$this->altKeys[$offset]]);
    }

    public function offsetUnset($offset):void {
        trigger_error("Unset of collector item $offset is not supported.", E_USER_NOTICE);
    }

    /*
     * Get item via array notation 
     * @param int|string $offset The item or alternate key
     * @see getItem()
     */
    public function offsetGet($offset): object {
        return $this->getItem($offset);
    }

    /* -------------------------------------------------------------------------
     * End of the arrayAccess interface implementation
     * ---------------------------------------------------------------------- */

    /**
     * Get all itmes 
     * @return AbstractCollector|AbstractCalculator[] 
     */
    public function getItems(): array {
        return $this->items;
    }

    /**
     * Returns the item at specified key.
     * @param int|string $key The item key or an alternate key.
     * @return AbstractCollector|AbstractCalculator Returns the required item
     */
    public function getItem($key) {
        $foundKey = $this->findItemKey($key);
        if ($foundKey !== false) {
            return $this->items[$foundKey];
        }
        trigger_error("Item '$key' does not exist.", E_USER_NOTICE);
    }

    /**
     * Adds values to related calculators or to other collectors. 
     * @param array $values Key represents the collector item or alternate key. 
     * Value is the value to be added or another array for recursive item strucures.
     * When item doesn't exist php raises a notice. 
     */
    public function add(array $values): void {
        foreach ($values as $key => $value) {
            if (isset($this->items[$key])) {
                $this->items[$key]->add($value);
            } else {
                $this->items[$this->altKeys[$key]]->add($value);
            }
        }
    }

    /**
     * Cumulate computed values to next higher group level.
     * Values of the current group level will be initialized with default values.
     */
    public function cumulateToNextLevel(): void {
        foreach ($this->items as $item) {
            $item->cumulateToNextLevel();
        }
    }

    /**
     * Extract ranges of items.
     *
     * Returns ranges of items located between start and end keys.
     * 
     * When a range is an array value1 is the start and value2 the end key.
     * When one of the keys don't exist the value of the altKey will be used instead.
     * When the items still doesn't exist an error will be thrown.
     * 
     * If start key equals Null the range begins at the first item.
     * When the end key equals Null the range ends at the last item.
     *   
     * When a range is not an array then the item with the corresponding key or
     * altKey is returned if it exist. If this doesn't exist php raise a notice.
     * 
     * Item keys are preserved. Sort order within ranges are preserved. Ranges
     * are returned in given order. When items belong to multiple ranges only
     * the first occurence will returned.
     * 
     * @param array|int|string[] $ranges Ranges or item keys for items to be filtered.
     * @return AbstractCollector Clone of current collector with selected items.
     * @throws InvalidArgumentException When start or end item doesn't exist.
     */
    public function range(... $ranges): AbstractCollector {
        $collector = clone $this;
        $collector->items = [];
        $keyOffsets = array_flip(array_keys($this->items));
        foreach ($ranges as $range) {
            if (is_array($range)) {
                $offset = (isset($range[0])) ? $this->getOffset($range[0], $keyOffsets) : 0;
                $length = (isset($range[1])) ? $this->getOffset($range[1], $keyOffsets) - $offset + 1 : null;
                $collector->items += array_slice($this->items, $offset, $length, true);
            } else {
                $key = $this->findItemKey($range);
                if ($key) {
                    $collector->items[$key] = $this->itmes[$key];
                }
            }
        }
        return $collector;
    }

    /**
     * Get the offest of an item by a given key.
     * @param int|string $key The item or alternate key
     * @param array $keyOffsets Array of integer offsets indexed by item keys.
     * @return int The offset of an item
     * @throws InvalidArgumentException
     */
    private function getOffset($key, array $keyOffsets) :int {
        if (isset($keyOffsets[$key])) {
            return $keyOffsets[$key];
        }
        if (isset($this->altKeys[$key], $this->itmes[$this->altKeys[$key]])) {
            return $keyOffsets[$this->altKeys[$key]];
        }
        throw new InvalidArgumentException("Key '$key' doesn't exist.");
    }

    /**
     * Find an item by key or alternate key.
     * When item is found by the given key this key will be returned. When the item
     * is found via the value of the alternate key will returned.
     * @param int|string $key The item or alternate key 
     * @return int|string|false The item key when an item exists. Else False.
     */
    private function findItemKey($key) {
        If (isset($this->items[$key])) {
            return $key;
        }
        return (isset($this->altKeys[$key], $this->items[$this->altKeys[$key]])) ? $this->altKeys[$key] : false;
    }

    /**
     * Filters items where key is between values.
     * 
     * Iterates over each collector item. If a range is an array and the item key
     * is between value1 and value2 of this range (inclusive) the item is returned.
     * 
     * If the range isn't an the item with the corresponding key is returned. 
     * 
     * If a range matches the key of a named range then the named range value will
     * be used to filter the items.
     * 
     * Item keys and sort order are preserved.
     *
     * @param array|int|string[] $ranges Ranges or item keys for items to be filtered.
     * @return AbstractCollector Clone of current collector with filtered items.
     */
    public function between(... $ranges): AbstractCollector {
        $collector = clone $this;
        $collector->items = [];
        foreach ($this->items as $key => $item) {
            foreach ($ranges as $range) {
                if (is_array($range)) {
                    if ((!isset($range[0]) || $key >= $range[0]) && (!isset($range[1]) || $key <= $range[1])) {
                        $collector->items[$key] = $item;
                        break;
                    }
                } else {
                    $key = $this->findItemKey($range);
                    if ($key) {
                        $collector->items[$key] = $item;
                        break;
                    }
                }
            }
        }
        return $collector;
    }

    /**
     * Filters items using a callback function.
     * Iterates over each item in the array passing key and value to the callback
     * function. If the callback function returns TRUE, the current item is returned
     * into the cloned collector. Item keys are preserved.
     * @param callable $callable The callback function to use. 
     * @return AbstractCollector Clone of current collector with filtered items
     */
    public function filter(callable $callable): AbstractCollector {
        $collector = clone $this;
        $collector->items = [];
        foreach ($this->items as $key => $value) {
            if ($callable($key, $value)) {
                $collector->items[$key] = $value;
            }
        }
        return $collector;
    }

    /**
     * Alter item collection by executing a php array command.
     * @param callable $command Any php array command which accepts an 
     * array as the first parameter. 
     * @param mixed[] $params Additional parameters passed to the php command.
     * @return AbstractCollector Clone of current collector with applied command
     * on the items array. 
     */
    public function cmd(callable $command, ...$params): AbstractCollector {
        $collector = clone $this;
        $result = $command($collector->items, ...$params);
        // When the command alters the array items will not replaced.
        if (is_array($result)) {
            $collector->items = $result;
        }
        return $collector;
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
