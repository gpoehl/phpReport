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
            return [Action::METHOD, $baseAction];
        }
        if ($baseAction instanceOf \Closure) {
            return [Action::CLOSURE, $baseAction];
        }
        if (is_array($baseAction)) {
            switch (count($baseAction)) {
                case 1:
                    $method = end($baseAction);
                    if (self::isValidName($method, true)) {
                        return [Action::METHOD, $method];
                    }
                    throw new \InvalidArgumentException("Invalid method name '$method' for $key action.");
                case 2:
                    if (self::isValidName($baseAction[1], true)) {
                        return [Action::CALLABLE, $baseAction];
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
            $action = strtolower($baseAction);
            if (str_starts_with($action, 'warning:')){
                return [Action::WARNING, substr($baseAction, 8)];
            }
            if (str_starts_with($action, 'error:')){
                return [Action::ERROR, substr($baseAction, 6)];
            }
        }
        if (str_starts_with($baseAction, ':')) {
            return [Action::STRING, substr($baseAction, 1)];
        }
        return (self::isValidName($baseAction, $allowPercentSign)) ? [Action::METHOD, $baseAction] : [Action::STRING, $baseAction];
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
     * @param string $replacement The replacement for the %sign
     * @param array $methodAction Method action is an array having the action type
     * in the first element and the action in the second element.
     * @return array The modified method action
     */
    public static function replacePercent(string $replacement, array $methodAction):array {
        if (is_string($methodAction[1]) && $methodAction[0] <> Action::CLOSURE) {
            $methodAction[1] = str_replace('%', $replacement, $methodAction[1]);
        }
        return $methodAction;
    }
  
}
