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

namespace gpoehl\phpReport\getter;

/**
 * GetterFactory instantiates getter objects which returns a value from data row
 */
class GetterFactory
{

    /**
     * @var  public bool $isObject True when data row is an object.
     * @var Object | Classname public $defaultTarget Default target for method calls when data isn't an
     * object or closure. 
     */
    public function __construct(public bool $isObject, public $defaultTarget = null) {
        
    }

    /**
     * Instantiate an getter object to retrieve a value from data row.
     * @param mixed $source The name of an attribute / field in the data row which
     * holds the desired value or method / closure which gets the value. 
     * @param array $params Optional parameter to be passed to closures and methods
     * @return BaseGetter Object which can get a value from data row.
     * @throws InvalidArgumentException
     */
    public function getGetter($source, $params): BaseGetter {
        if ($source instanceOf \Closure) {
            return new GetFromCallable($source, $params);
        }
        if (!is_array($source)) {
            return ($this->isObject) ? new GetFromObjectProperty($source, $params) : new GetFromArray($source, $params);
        }
        switch (count($source)) {
            case 1:
                // Default target for objects is the object itself. Else it's the
                // For arrays or strings the $defaultTarget.
                $source = current($source);
                return ($this->isObject) ? new GetFromObjectMethod($source, $params) : new GetFromCallable([$this->defaultTarget, $source], $params);
            case 2:
                return new GetFromCallable($source, $params);
            default:
                throw new \InvalidArgumentException("Source parameter array must have only one or two elements.");
        }
    }

    /**
     * Instantiate an getter object to retrieve a key and data value from data row
     * for sheets.
     * When only the key is given the getter class must return an associated array
     * in the form [$key => $value]. 
     * @param mixed $keySource The name of an attribute / field in the data row which
     * holds the desired value or method / closure which gets the key value. 
     * @param mixed $valueSource The name of an attribute / field in the data row which
     * holds the desired value or method / closure which gets the data value. 
     * @param array $params Optional parameter to be passed to closures and methods
     * @return BaseGetter Object which can get key and value from data row.
     */
    public function getSheetGetter($keySource, $valueSource, $params): BaseGetter {
        if ($valueSource === null) {
            return $this->getGetter($keySource, $params);
        }
        return new GetForSheet([$this->getGetter($keySource, $params), $this->getGetter($valueSource, $params)], $params);
    }

}
