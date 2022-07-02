<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright © Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport\output;

use gpoehl\phpReport\CumulateIF;

/**
 * BandOutput maintains output in arrays which can be treated as bands in
 * common report generators.
 *
 * Instead of creating a new array dimension for each group a flat array will be used.
 * This allows easy access to the latest output value of any group.
 *
 * Use this class to manipulate the output, get parts of the output or to write
 * output directly into any of the bands (e.g. write into the header from a footer
 * group or create summaries).
 *
 * Each group level can have it's own set of bands.
 *
 * This class runs at nearly the same speed as the StringOutput class but needs
 * more peak memory when strings are build from array elements.
 */
class BandOutput extends AbstractOutput implements CumulateIF
{
/**
 * @var $output[][][]. $values saved via write() are stored indexed by
 * group level, band key. Last key is a non associated array key for added elements.
 */
    private array $output = [];
    // Map action keys to numeric keys. Output will be sorted by this keys.

    public array $actionKeyMapper = [
        'init' => self::HEADER,
        'totalHeader' => self::HEADER,
        'beforeGroup' => self::HEADER,
        'groupHeader' => self::HEADER,
        'detailHeader' => self::HEADER,
        'detail' => self::DATA,
        'detailFooter' => self::FOOTER,
        'groupFooter' => self::FOOTER,
        'afterGroup' => self::FOOTER,
        'totalFooter' => self::FOOTER,
        'close' => self::FOOTER,
        'noData' => self::DATA,
        'noData_n' => self::DATA,
        'detail_n' => self::DATA,
        'noGroupChange_n' => self::DATA,
    ];

    // The band keys by which the output will be sorted.
    public const HEADER = 1;
    public const SUMMARYHEADER = 2;
    public const SUMMARYDATA = 3;
    public const SUMMARYFOOTER = 4;
    public const DATA = 5;
    public const BOTTOMSUMMARYHEADER = 6;
    public const BOTTOMSUMMARYDATA = 7;
    public const BOTTOMSUMMARYFOOTER = 8;
    public const FOOTER = 9;

    /**
     * Write (append) a value to output
     * @param mixed $value The value to be written.
     * Anything that can be used by the implode command.
     * @param int $level The group level.
     * @param $bandKey The band key within a group.
     */
    public function write($value, int $level, int $bandKey = self::DATA) {
        $this->output[$level][$bandKey][] = $value;
    }

    /**
     * Prepend value to the band key of given level.
     * When output at $key and $bandKey doesn't exist then a new entry will be
     * created.
     * @param mixed $value The value to be prepended.
     * Anything that can be used by the implode command.
     * @param int $level The group level.
     * @param $bandKey The band key within a group. Action keys will
     * be replaced by the values of $actionKeyMapper.
     */
    public function prepend($value, int $level, int $bandKey) {
        if (isset($this->output[$level][$bandKey])) {
            array_unshift($this->output[$level][$bandKey], $value);
        } else {
            $this->output[$level][$bandKey][] = $value;
        }
    }

    /**
     * Return the raw output.
     * @param int|null $level The group level. When null output for all levels
     * will be returned.
     * @param $bandKey The band key for which the output will be selected.
     * @return array | null The part of the output array matching $level and $key
     */
    public function getRawOutput(?int $level = null, ?int $bandKey = null): ?array {
        if ($level === null) {
            return $this->output;
        }
        if ($bandKey === null) {
            return $this->output[$level] ?? null;
        }
        return $this->output[$level][$bandKey] ?? null;
    }

    /**
     * Get the output value as string
     * @param int|null $level The requested group level. All data belonging
     * to this level (having a level > the requested level) will also be returned
     * when the $bandKey is null.
     * @param $bandKey The band key for which the output will be selected.
     * When given only the level which equals the given level will be returned.
     * @return string|null The prepared output string
     */
    public function get(int $level = 0, ?int $bandKey = null): ?string {
        $level ??= 0;

        // When key is set only the specified array element will be returned
        if ($bandKey !== null) {
//            $bandKey = $this->actionKeyMapper[$bandKey] ?? $bandKey;
            if (isset($this->output[$level][$bandKey])) {
                return implode($this->separator, $this->output[$level][$bandKey]);
            }
            return null;
        }
        // Iterate over group level and return anything equal or lower of the given $level
        foreach (array_keys($this->output) as $key => $value) {
            if ($value >= $level) {
                $wrk = array_slice($this->output, $key, null, true);
                break;
            }
        }
        if (!isset($wrk)) {
            return null;
        }

        // Cumulate lover levels up to requested level
        // The call is also required for empty levels
        for ($i = array_key_last($wrk); $i >= $level; $i--) {
            $this->cumulateToNextLevel($i, $wrk);
        }
        // returns the build string. Note: 3 times current().
        return current(current(current($wrk)));
    }

    /**
     * Pops and returns output from the given level.
     * When key is given only the requried key will be popped and returned.
     * @param int|null $level The group level
     * @param int|string|null $bandKey
     */
    public function pop(int $level = 0, ?int $bandKey = null): ?string {
        $wrk = $this->get($level, $bandKey);
        $this->delete($level, $bandKey);
        return $wrk;
    }

    /**
     * Deletes output from the given level or bandKey.
     * When bandKey is given only the requried bandKey will be deleted.
     * @param int|null $level The group level. When bandKey is null the requsted
     * level and all included levels will be deleted.
     * @param int|string|null $bandKey
     */
    public function delete(int $level = 0, ?int $bandKey = null): void {
        // Delete specific band in level
        if ($bandKey !== null) {
            $bandKey = $this->actionKeyMapper[$bandKey] ?? $bandKey;
            unset($this->output[$level][$bandKey]);
            return;
        }
        // Delete given level and included levels
        foreach ($this->output as $key => $value) {
            if ($key >= $level) {
                unset($this->output[$key]);
            }
        }
    }

    /**
     * Cumulate value from given level to next higher level.
     * Values are written to the detail band of the higher level.
     * @param int $level The level to be cumulated
     * @param array | null $arr The array to be cumulated. When this method is
     * called from the report class the $arr is null. In this case it will be
     * set the $this->output.
     * Note the value is passed as reference and will be altered.
     */
    public function cumulateToNextLevel(int $level, ?array &$arr = null): void {
        if ($arr === null) {
            $arr = &$this->output;
        }
        if (isset($arr[$level])) {
            $wrk = [];
            // Sort the array by bandKey. Required as one can write to any bandKey
            // and php doesn't store them in numeric sequence
            ksort($arr[$level], SORT_NUMERIC);
            foreach ($arr[$level] as $val) {
                $wrk[] = implode($this->separator, $val);
            }
            // Write to detail in higher level.
            $arr[$level - 1][self::DATA][] = implode($this->separator, $wrk);
            unset($arr[$level]);
        }
    }

    public function __toString() {
        return $this->get();
    }

}
