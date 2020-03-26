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
 * Collection of static helper methods
 */
class Helper {

    // Source types
    const ATTRIBUTE = 1;
    const CLOSURE = 2;
    const METHOD = 3;
    const CLASSMETHOD = 4;
    const SHEETATTRIBUTES = 5;

    // pattern to check for valid method names (taken from php documentation)
    // pattern_n extends pattern to accept also the % sign
    static $pattern = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/";
    static $pattern_n = "/^[a-zA-Z_%\x7f-\xff][a-zA-Z0-9_%\x7f-\xff]*$/";

    /**
     * Evaluate the action typ and build the method action.
     * Depending of the value of $baseAction the action typ will be set.
     * @param mixed $baseAction Base or raw action set by configuration 
     * @param string $key The action key
     * @param bool $allowPercentSign Is the % sign allowed in $baseAction. Defaults to false
     * @return array Array having the action typ and $baseAction 
     * @throws InvalidArgumentException
     */
    public static function buildMethodAction($baseAction, string $key, bool $allowPercentSign = false): array {
        if ($baseAction === false) {
            return [Report::METHOD, $baseAction];
        }
        if ($baseAction instanceOf \Closure) {
            return [Report::CLOSURE, $baseAction];
        }
        if (is_array($baseAction)) {
            switch (count($baseAction)) {
                case 1:
                    $method = end($baseAction);
                    if (self::isValidName($method, true)) {
                        return [Report::METHOD, $method];
                    }
                    throw new \InvalidArgumentException("Invalid method name '$method' for $key action.");
                case 2:
                    if (self::isValidName($baseAction[1], true)) {
                        return [Report::CALLABLE, $baseAction];
                    }
                    throw new \InvalidArgumentException("Second parameter in callable '{$baseAction[1]}' is invalid for '$key'.");
                default:
                    throw new \InvalidArgumentException("Callable array for '$key' has more than two elements");
            }
        }
        // Base action is not an array or closure
        return self::buildMethodActionFromString($baseAction, $key, $allowPercentSign);
    }

    /**
     * Check if the given value should be handled as a method name or as a string 
     * Result of this check is returned in the first array element.
     * A colon (:) at beginning of value forces the value to be a normal string.
     * In this case the colon is truncated.
     * @param string $value The string to be checked
     * @return array First element is a boolean indicating if $value is a
     * string while second value has the value without trailing colon.
     */
    private static function buildMethodActionFromString(string $baseAction, string $key, bool $allowPercentSign): array {
        if ($key === 'noGroupChange_n') {
            if (strtolower(substr($baseAction, 0, 8)) === 'warning:') {
                return [Report::WARNING, substr($baseAction, 8)];
            }
            if (strtolower(substr($baseAction, 0, 6)) === 'error:') {
                return [Report::ERROR, substr($baseAction, 6)];
            }
        }
        if (substr($baseAction, 0, 1) === ':') {
            return [Report::STRING, substr($baseAction, 1)];
        }
        return (self::isValidName($baseAction, $allowPercentSign)) ? [Report::METHOD, $baseAction] : [Report::STRING, $baseAction];
    }

    /**
     * Verify if a given string can be used as attribute or method name
     * @param string $name The name to be verified
     * @param bool $allowPercentSign Is the % sign allowed in $name. Defaults to false.
     * @return bool True when $name is valid, false when not.
     */
    public static function isValidName(string $name, bool $allowPercentSign = false): bool {
        if ($allowPercentSign) {
            return (bool) preg_match(self::$pattern_n, $name);
        }
        return (bool) preg_match(self::$pattern, $name);
    }

    /**
     * Replace percent sign (%) in method action.
     * % sign in callables will not be replaced. 
     * @param string|int $replacemet The replacement for the %sign
     * @param array $methodAction Method action is an array having the action type
     * in the first element and the action in the second element.
     * @return array The modified method action
     */
    public static function replacePercent($replacemet, array $methodAction) {
        if (is_string($methodAction[1]) && $methodAction[0] <> Report::CLOSURE) {
            $methodAction[1] = str_replace('%', $replacemet, $methodAction[1]);
        }
        return $methodAction;
    }

    /**
     * Determine the source type of a given source
     * Source type simplifies access to source values.
     * To make $source a callable put the parameters into an array.
     * If the callable has one element the related class will be the $target
     * class or the current row object. In this case the returned source parameter
     * will be a scalar value of the array element. 
     * @param mixed $source
     * @return array First array element is the source type. The second element
     * the modified source parameter. 
     */
    public static function getSourceType($source) {
        if ($source instanceof \Closure) {
            return [self::CLOSURE, $source];
        }
        return (is_array($source)) ? self::getMethodType($source) : [self::ATTRIBUTE, $source];
    }

    public static function getMethodType(array $source) {
        switch (count($source)) {
            case 1:
                // return the single array element as scalar value
                return [self::METHOD, end($source)];
            case 2:
                if ($source [0] === null) {
                    return [self::METHOD, $source[1]];
                } elseIf ($source [1] === null) {
                    return [self::METHOD, $source[0]];
                } else {
                    return [self::CLASSMETHOD, $source];
                }
            default: throw new \InvalidArgumentException("Invalid callable. Must have 1 or 2 elements. " . count($source) . " elements given.");
        }
    }

    /**
     * Determine the source type of a given source
     * Source type simplifies access to source values.
     * Compared to the getSoruceType method $source usually is an array to get two
     * values from the source. 
     * To indicate that a method should be called wrap the class / method names 
     * into an array at the first position of an surrounding array. E.g. [[class, method]] 
     * When the inner array has only one element the value will be taken form a default
     * class. In this case the returned source parameter will be a scalar value
     * of the array element. 
     * @param mixed $source
     * @return array First array element is the source type. The second element
     * the modified source parameter. 
     */
    public static function getSheetSourceType($source) {
        if ($source instanceof \Closure) {
            return [self::CLOSURE, $source];
        }
        if (!is_array($source)) {
            throw new \InvalidArgumentException("Source must be a closure or an array.");
        }

        if (count($source) === 1) {
            if (is_array(current($source))) {
                // first array element is an array and will be handled as a callable 
                return self::getMethodType($source[0]);
            }
            // allow [key=>value] and [$key, $value]
            return [self::SHEETATTRIBUTES, [key($source), current($source)]];
        }
        if (count($source) === 2) {
            return [self::SHEETATTRIBUTES, $source];
        }
        throw new \InvalidArgumentException("Sheet attributes must contain reference to key and value.");
    }

}
