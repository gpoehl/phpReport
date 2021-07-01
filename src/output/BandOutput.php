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
 * Use this class when you want to manipulate the output, get parts of the output
 * or to write output directly into any of the bands (e.g. write into the header
 * from a footer group).
 * Note that pphpReport doesn't ivoke any summary actions. To write summary data you need to 
 * call a write method. 
 * Each group level might get own summary data.
 * 
 * This class runs at nearly the same speed as the StringOutput class but needs 
 * more peak memory when strings are generatged from array elements.
 */
class BandOutput extends AbstractOutput implements CumulateIF
{

    public array $output = [];
    
    // Details in last Group or with extra level ??????????????????????? 
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
    
    public const HEADER = 1;
    public const SUMMARYHEADER = 2;
    public const SUMMARYDATA = 3;
    public const SUMMARYFOOTER = 4;
   
    public const DATA = 5;
    public const FOOTER = 6;


    public function write($value, int $level, $key) {
        $this->output[$level][$key][] = $value;
    }

    /**
     * Prepend value to the actionKey of given level.
     * Within an action the output is always returned in right order of action keys. 
     * So calling this method is often not not required.
     */
    public function prepend($value, int $level = -1, $key = 1) {
        if (is_numeric($key)) {
            $key = $this->actionKeyMapper[key];
        }
        array_unshift($this->output[$level][$key], $value);
    }

    public function getRawOutput(?int $level = null, ?string $key = null) {
        if ($level === null) {
            return $this->output;
        }
        if ($key === null) {
            return $this->output[$level];
        }
        return ($this->output[$level][$key]);
    }

    /**
     * Get the output value as string
     * @param int|null $level The requested group level
     * @param string|null $key
     * @return type
     */
    public function get(?int $level = null, ?string $key = null) {
        $level ??= -1;

        // When key is set only the specified array element will be returned
        if ($key !== null) {
            if (is_string($key)) {
                $key = $this->actionKeyMapper[$key];
            }
            if (isset($this->output[$level][$key])) {
                return implode($this->separator, $this->output[$level][$key]);
            }
            return null;
        }

        foreach (array_keys($this->output) as $key => $value) {
            if ($value >= $level) {
                $wrk = array_slice($this->output, $key, null, true);
                break;
            }
        }
        if (!isset($wrk)) {
            return null;
        }

        // Loop from last level to requested level
        // The call is also required when for empty levels
        for ($i = array_key_last($wrk); $i >= $level; $i--) {
            $this->cumulateToNextLevel($i, $wrk);
        }
        return current(current(current($wrk)));
    }

    /**
     * Pops and returns output from the given level.
     * When key is given only the requried key will be popped and returned.
     * @param int|null $level The group level
     * @param string|null $key
     */
    public function pop(?int $level = null, ?string $key = null): ?string {
        $wrk = $this->get($level, $key);
        $this->delete($level, $key);
        return $wrk;
    }

    /**
     * Deletes output from the given level.
     * When key is given only the requried key will be deleted.
     * @param int|null $level The group level
     * @param string|null $key
     */
    public function delete(?int $level = null, ?string $key = null): void {
        $level ??= -1;
        if ($key !== null) {
            if (is_string($key)) {
                $key = $this->actionKeyMapper[$key];
            }
            unset($this->output[$level][$key]);
            return;
        }
        // Can't use array_splice. Key
        foreach ($this->output as $key => $value) {
            if ($key >= $level) {
                unset($this->output[$key]);
            }
        }
    }

//     public function deleteSplice(?int $level = null, ?string $key = null) :void{
//    function array_splice_preserve_keys(&$input, $offset, $length = null, $replacement = array()) {
//        if (empty($replacement)) {
//            return array_splice($input, $offset, $length);
//        }
//
//        $part_before = array_slice($input, 0, $offset, $preserve_keys = true);
//        $part_removed = array_slice($input, $offset, $length, $preserve_keys = true);
//        $part_after = array_slice($input, $offset + $length, null, $preserve_keys = true);
//
//        $input = $part_before + $replacement + $part_after;
//
//        return $part_removed;
//
//// use as normal
////array_splice_preserve_keys($input, $offset, $length, $replacement);
//    }

    /**
     * Cumulate value from givel leven to higher level.
     * Values are written to the detail of the higher level.
     * @param int $level The level to be cumulated
     * @param array | null $arr The array to be worked on. Not given from 
     * report class. 
     */
    public function cumulateToNextLevel(int $level, ?array &$arr = null): void {
        // Function is also used from get(). So a reference to $arr is given.
        // $arr will be changed. So a reference to $this->output is requried
        if ($arr === null) {
            $arr = &$this->output;
        }
        if (isset($arr[$level])) {
            $wrk = [];
            // Sort the array. Header might be written after detail.
            // Getting array elements by key has similar performance.
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
