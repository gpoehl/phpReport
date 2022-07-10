<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport\Getter;

/**
 * Choose and instantiate a getter object which gets value.
 */
class GetterFactory
{
    /* @var $isJoin True for join method. Will select getter which suppresses warnings when array item is missing. */

    public bool $isJoin = false;

    /**
     * @param $isObject True when data row is an object.
     * @param Object | Classname | null $defaultTarget Default target when
     * target element in source equals true. Usually the same as in report class.
     * @param className The name of the row class. Used to access the row class
     * members static properies, constants and static methods expeting $row and
     * $rowKey parameters.
     */
    public function __construct(private bool $isObject, private $defaultTarget = null,
            private ?string $className = '') {

    }

    /**
     * Choose and instantiate an getter object to retrieve a value specified by $source.
     * @param mixed $source The source from which the value should be get from.
     * When $source is an array the elements might be named as name, target and selector.
     * Hint: Call verifySource before to check that it is properly set.
     * @param array $params Parameters to be passed unpacked to closures and methods
     * @return GetValueInterface Object which implements the GetValueInterface.
     * @throws InvalidArgumentException
     */
    public function getGetter($source, array $params): GetValueInterface {
        if (!is_array($source)) {
            return match (true) {
                $source instanceof GetValueInterface => $source,
                $source instanceOf \Closure => new GetFromCallable($source, $params),
                $this->isObject => new GetRowProperty($source),
                $this->isJoin === false => new GetArrayItem($source),
                $this->isJoin => new GetArrayItemForJoin($source),
            };
        }

        $source[1] ??= null;
        $source[2] ??= null;
        [$name, $target, $selector] = $source;

        if ($name instanceOf \Closure) {
            return ($selector) ?
                    new GetFromCallable($name, $params) :
                    new GetFromPureCallable($name, $params);
        }

        // Row method not getting $row and $rowKey as parameters
        if ($target === null && ($selector === null || $selector === false)) {
            return new GetFromRowMethod($name, $params);
        }

        $target = match ($target) {
            null => $this->className,
            true => $this->defaultTarget,
            default => $target
        };

        try {
            return match (true) {
            // Object or row method getting $row and $rowKey.
            // Requesting $row and $rowKey on row class methods is handled
            // as static method. Regular methods don't need $row.
                $selector === true => new GetFromCallable([$target, $name], $params),
                // Object property
                $selector === null => new GetProperty([$target, $name]),
                // Callable not expecting $row and $rowKey
                $selector === false => new GetFromPureCallable([$target, $name], $params),
                preg_match('/stat/i', $selector) === 1 => new GetStaticProperty([$target, $name]),
                // Constant requires classname
                preg_match('/const/i', $selector) === 1 => new GetConstant(((is_object($target)) ? $target::class : $target) . '::' . $name),
            };
        } catch (\UnhandledMatchError $e) {
            $name = print_r($name, true);
            self::handleError("Invalid source selector for '$name'.");
        }
    }

    /**
     * Instantiate an getter object to retrieve a key and data value for sheets.
     * When only the key is given the getter class must return an associated array
     * in the form [$key => $value].
     * @param mixed $keySource The source for the sheet key.
     * @param mixed $valueSource The source for the sheet value. Null when
     * $keySource returns a [$key => $value] array.
     * @param array $keyParams Parameters will be unpacked when passing to $keySource callables.
     * @param array $params Parameters will be unpacked when passing to $source callables.
     * @return BaseGetter Object which can get key and value from data row.
     */
    public function getSheetGetter($keySource, $source, array $keyParams, array $params): BaseGetter {
        if ($source === null) {
            return $this->getGetter($keySource, $keyParams);
        }
        return new GetForSheet(['keyGetter' => $this->getGetter($keySource, $keyParams),
            'valueGetter' => $this->getGetter($source, $params)]);
    }

    /**
     * Verify sources before knowing which data type the data row will be.
     * The verification is not integrated in the getGetter methods to raise the
     * error or warning as soon as possible. Final decision can be made only
     * after reading first row per dimension.
     * @param mixed $source The source of data value.
     * @param array $params Parameters to be passed to callables. When source
     * is not a callable $params should be an empty array.
     * To verify sources for sheets always pass empty params as they will be used
     * for key and value of the sheet.
     * @return bool Always true when no warning or error has been encountered.
     */
    public static function verifySource($source, array $params): bool {

        if (!is_array($source)) {
            if ($source instanceOf \Closure) {
                return true;
            }
            // When soruce is object it must be a BaseGetter
            if (is_object($source)) {
                if ($source instanceof GetValueInterface) {
                    return true;
                }
                self::handleError("Source object must implement the GetValueInterface.");
            }
            // Property from row object or row array item
            if (is_string($source) || is_int($source) || is_bool($source)) {
                return self::verifyThatParmeterIsEmpty($source, $params, 'row property');
            }
            self::handleError("Scalar source is not a closure, BaseGetter, string, int or bool.");
        }

        $counter = count($source);
        if ($counter > 3) {
            self::handleError("Invalid source. $counter elements given, max. 3 expected.");
        } elseif ($counter == 0) {
            self::handleError("Invalid source. Empty array given.");
        }

        $source[1] ??= null;
        $source[2] ??= null;

        if (array_keys($source) !== [0, 1, 2]) {
            self::handleError("Associated source array elements are given.");
        }

        if ($source[0] === null) {
            self::handleError("Source name element is null.");
        }

        if ($source[0] instanceof gpoehl\phpReport\getter\GetValueInterface) {
            if ($source[1] !== null || source[2] !== null) {
                self::handleError("Additonal source elements for objects implementing GetValueInterface will be ignored.", true);
            }
            return true;
        }

        if ($source[0] instanceOf \Closure) {
            if ($source[1] !== null) {
                self::handleError("Second source element for closure will be ignored.", true);
            }
            if ($source[2] !== null && !is_bool($source[2])) {
                self::handleError("Third source element for closure must be null or boolean.");
            }
            return true;
        }

        // Row method
        if ($source[1] === null && ($source[2] === null || is_bool($source[2]))) {
            return true;
        }

        // Property of any object
        if ($source[2] === null) {
            return self::verifyThatParmeterIsEmpty($source[0], $params, 'object property');
        }

        // Any method
        if (is_bool($source[2])) {
            return true;
        }

        if (preg_match('/const/i', $source[2])) {
            return self::verifyThatParmeterIsEmpty($source[0], $params, 'constant');
        }
        if (preg_match('/stat/i', $source[2])) {
            return self::verifyThatParmeterIsEmpty($source[0], $params, 'static property');
        }

        self::handleError("Invalid source parameters");
    }

    /**
     * Verify the parameters are only given for callables but not for properties or constants
     * @param mixed $source The source or first $source element (name)
     * @param array $params The params parameter related to the source. Should be empty
     * @param string $msg Additional message text.
     * @return bool
     */
    private static function verifyThatParmeterIsEmpty($source, array $params, string $msg): bool {
        $name = print_r($source, true);
        if (!empty($params)) {
            self::handleError("Parameters will be ignored while getting value for $msg '$name'.", true);
        }
        return true;
    }

    /**
     * Throw error or raise a warning.
     * @param string $message The error or warning message.
     * @param bool $isWarning When false an error will be throw.
     * @throws InvalidArgumentException or triggers warning
     */
    private static function handleError(string $message, bool $isWarning = false) {
        if ($isWarning) {
            trigger_error($message, E_USER_WARNING);
        } else {
            throw new \InvalidArgumentException($message);
        }
    }

}
