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
use gpoehl\phpReport\Calculator\AbstractCalculator;
use gpoehl\phpReport\Calculator\PrecisionTrait;
use InvalidArgumentException;

/**
 * Base class of Collector and sheet classes.
 * Class is declared as abstract to avoid instantiation. It has no abstract
 * methods.
 */
abstract class AbstractCollector implements ArrayAccess
{

    use PrecisionTrait;

    /** @var Array of calculator or collector objects. */
    public array $items = [];

    /** @var Array of alternate keys to access items. */
    protected array $altKeys = [];

    /**
     * Set alternate keys to access items.
     * @param int|string[] $keys Item keys indexed by alternate keys.
     */
    public function setAltKeys(array $keys): void {
        foreach ($keys as $key => $itemKey) {
            $this->setAltKey($key, $itemKey);
        }
    }

    /**
     * Set alternate key for an item.
     * @param $key Unique alternate key.
     * @param $itemKey The key of the item in $items.
     */
    public function setAltKey(int|string $key, int|string $itemKey): void {
        if (isset($this->items[$key]) || isset($this->altKeys[$key])) {
            throw new InvalidArgumentException("Key '$key' already exists.");
        }
        $this->altKeys[$key] = $itemKey;
    }

    /**
     * Magic get method to access item via arrow notation.
     * Note: A magic setter method is not implemented. Use the addItem method instead.
     * Example: $collector->itemKey
     * @param int|string $key the item key
     * @return AbstractCollector|AbstractCalculator Returns the requested item
     */
    public function __get($key): object {
        return $this->getItem($key);
    }

    /* -------------------------------------------------------------------------
     * Implementation of the arrayAccess interface
     * ---------------------------------------------------------------------- */

    /**
     * Add new item to items array via array notation
     * @param int|string $offset The item key
     * @param AbstractCollector|AbstractCalculator $value The item
     */
    public function offsetSet($offset, $value): void {
        $this->addItem($value, $offset);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->items[$offset]) || isset($this->items[$this->altKeys[$offset]]);
    }

    public function offsetUnset($offset): void {
        throw new \Exception("Unset of collector item $offset is not supported.");
    }

    /**
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
     * Get the item at specified key.
     * @param $key The item key or an alternate key.
     */
    public function getItem(int|string $key): AbstractCollector|AbstractCalculator {
        $foundKey = $this->findItemKey($key);
        if ($foundKey !== false) {
            return $this->items[$foundKey];
        }
        throw new \InvalidArgumentException("Item '$key' doesn't exist.");
    }

    /**
     * Adds values to related calculators or to other collectors.
     * @param $values Key represents the collector item or alternate key.
     * Value is the value to be added or another array for recursive item structures.
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
    public function range(...$ranges): AbstractCollector {
        $collector = clone $this;
        $collector->items = [];
        $keyOffsets = array_flip(array_keys($this->items));
        foreach ($ranges as $range) {
            if (is_array($range)) {
                $offset = (isset($range[0])) ? $this->getOffset($range[0], $keyOffsets) : 0;
                $length = (isset($range[1])) ? $this->getOffset($range[1], $keyOffsets) - $offset + 1 : null;
                $collector->items += array_slice($this->items, $offset, $length, true);
            } elseif (isset($this->items[$range])) {
                $collector->items[$range] = $this->items[$range];
            } elseif (isset($this->altKeys[$range], $this->items[$this->altKeys[$range]])) {
                $collector->items[$this->altKeys[$range]] = $this->items[$this->altKeys[$range]];
            } else {
                throw new \InvalidArgumentException("Item '$range' doesn't exist.");
            }
        }
        return $collector;
    }

    /**
     * Get the offset of an item by a given key.
     * @param $key The item or alternate key
     * @param array $keyOffsets Array of integer offsets indexed by item keys.
     * @return int The offset of an item
     * @throws InvalidArgumentException
     */
    private function getOffset(int|string $key, array $keyOffsets): int {
        if (isset($keyOffsets[$key])) {
            return $keyOffsets[$key];
        }
        if (isset($this->altKeys[$key], $this->items[$this->altKeys[$key]])) {
            return $keyOffsets[$this->altKeys[$key]];
        }
        throw new InvalidArgumentException("Item '$key' doesn't exist.");
    }

    /**
     * Find an item by key or alternate key.
     * When item is found by the given key this key will be returned. When the item
     * is found via the value of the alternate key will returned.
     * @param $key The item or alternate key
     * @return int|string|false The item key when an item exists. Else False.
     */
    private function findItemKey(int|string $key) {
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
    public function between(...$ranges): AbstractCollector {
        $collector = clone $this;
        $collector->items = [];
        foreach ($this->items as $key => $item) {
            foreach ($ranges as $range) {
                if (is_array($range)) {
                    if ((!isset($range[0]) || $key >= $range[0]) && ((!isset($range[1])) || $key <= $range[1])) {
                        $collector->items[$key] = $item;
                        break;
                    }
                } elseIf ($range === $key || (isset($this->altKeys[$range]) && $this->altKeys[$range] === $key)) {
                    $collector->items[$key] = $item;
                    unset($ranges[$range]);
                    break;
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
        foreach ($this->items as $key => $item) {
            if ($callable($key, $item)) {
                $collector->items[$key] = $item;
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
     * Aggregate methods
     * Note: All related calculators must implement the the requested methods.
     * ---------------------------------------------------------------------- */

    public function sum($level = null, int $depth = 0) {
        return $this->getTotal('sum', $depth, 0, $level);
    }

    public function count($level = null, int $depth = 0) {
        return $this->getTotal('count', $depth, 0, $level);
    }

    public function countNN($level = null, int $depth = 0) {
        return $this->getTotal('countNN', $depth, 0, $level);
    }

    public function countNZ($level = null, int $depth = 0) {
        return $this->getTotal('countNZ', $depth, 0, $level);
    }

    /**
     *
     * Averages are not yet correctly implemented.
     */
    public function avg($level = null, int $depth = 0) {
        return $this->getAvg('avg', $depth, 0, $level);
    }

    public function avgNN($level = null, int $depth = 0) {
        return $this->getAvg('avgNN', $depth, 0, $level);
    }

    public function avgNZ($level = null, int $depth = 0) {
        return $this->getAvg('avgNZ', $depth, 0, $level);
    }

    /**
     * Min and max are not yet tested!
     * @param type $level
     * @param bool $asArray
     * @return type
     */
    public function min($level = null, int $depth = 0): array|int|float|string|null {
        return $this->getMinMax('min', $depth, 0, $level, min(...));
    }

    public function max($level = null, int $depth = 0): array|int|float|string|null {
        return $this->getMinMax('max', $depth, 0, $level, max(...));
    }

    public function getMinMax(string $typ, int $depth, int $currentDepth, $level, $func, $sum = null,): array|int|float|string|null {
        if ($depth > 0) {
            $currentDepth++;
            $sum ??= [];
            foreach ($this->items as $key => $item) {
                if ($currentDepth < $depth && ($item instanceof AbstractCollector)) {
                    $sum[$key] = $item->getMinMax($typ, $depth, $currentDepth, $level, $func, $sum,);
                } else {
                    $sum[$key] = $item->$typ($level);
                }
            }
            return $sum;
        }
        // Prepare scalar result. Values will be compares as they are.
        // No conversion from bcm string to float or vice versa.
        $wrk = [];
        foreach ($this->items as $key => $item) {
            $res = $item->$typ($level);
            if ($res !== null) {
                $wrk[] = $res;
            }
        }
        return (empty($wrk)) ? null : $func($wrk);
    }

    /**
     * Calculate the total of sum or counters.
     * @param string $typ Method name to be called on items.
     * @param bool $asArray Build result as array or as scalar value
     * @param $level The group level
     * @param ?int $scale Set scale only for 'sum' to keep counters as integers.
     * @return array|int|float|string The calculated value.
     */
    public function getTotal(string $typ, int $depth, int $currentDepth, $level, $sum = null): array|int|float|string {
        if ($depth > 0) {
            $currentDepth++;
            $sum ??= [];
            foreach ($this->items as $key => $item) {
                if ($currentDepth < $depth && ($item instanceof AbstractCollector)) {
                    $sum[$key] = $item->getTotal($typ, $depth, $currentDepth, $level, $sum);
                } else {
                    $sum[$key] = $item->$typ($level);
                }
            }
            return $sum;
        }
        // Prepare scalar result. Counters will not use BCMath funcitons.
        if ($this->scale === null || $typ !== 'sum') {
            $sum = 0;
            foreach ($this->items as $key => $item) {
                $sum += $item->$typ($level);
            }
            return $sum;
        }
        // Scalar BCMath result for sum().
        $sum = $this->zero;
        foreach ($this->items as $key => $item) {
            // cast param2 to string. Collector might not return string
            $sum = bcadd($sum, (string) $item->$typ($level), $this->scale);
        }
        return $sum;
    }

    /**
     * Get the average of calculated values.
     * @param $level The requested level. Defaults to the current level.
     * @param $counter The requested counter to calculate the requested average.
     * @return The average of calculated values at the requested level.
     */
    protected function getAvg(string $typ, int $depth, int $currentDepth, $level, $sum = null): array|int|float|string {
        if ($depth > 0) {
            $currentDepth++;
            $sum ??= [];
            foreach ($this->items as $key => $item) {
                if ($currentDepth < $depth && ($item instanceof AbstractCollector)) {
                    $sum[$key] = $item->getAvg($typ, $depth, $currentDepth, $level, $sum);
                } else {
                    $sum[$key] = $item->$typ($level);
                }
            }
            return $sum;
        }
        // Prepare scalar result. Null when divisor is 0.
        $divisor = $this->getTotal('count' . substr($typ, 3), $depth, $currentDepth, $level);

        return ($divisor == 0) ? null : ($this->scale === null ?
                $this->getTotal('sum', $depth, $currentDepth, $level) / $divisor
                // BCMath
                : bcdiv($this->getTotal('sum', $depth, $currentDepth, $level, $this->scale), (string) $divisor, $this->scale));
    }

}
