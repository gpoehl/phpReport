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
 * Collection of dimensions.
 */
class Dimensions extends \ArrayIterator {

    /**
     * @var Dimension ID's indexed by name
     */
    public array $names = [];

    /**
     * Don't allow constructor parameter to force usage of add() method. 
     * @param type $array
     * @throws \Exception
     */
    public function __construct($array = []) {
        if (!empty($array)) {
            throw new \Exception('Use add method.');
        }
    }

    /**
     * @return Dimension
     */
    public function current(): Dimension {
        return parent::current();
    }

    public function offsetGet($offset): Dimension {
        return parent::offsetGet($offset);
    }

    /**
     * Add a dimension object
     * @throws InvalidArgumentException when dimension name exists
     */
     public function add(Dimension $dimension): void {
        if (!empty($this->names)) {
            if (array_key_exists($dimension->name, $this->names)) {
                throw new \Exception("Dimension name '{$dimension->name}' already exists.");
            }
            $currentDim = $this->current();
            $currentDim->isLastDim = false;
            $dimension->lastLevel = $currentDim->lastLevel;
            $dimension->id = $this->count();
        }
        $this->names[$dimension->name] = $dimension->id;
        parent::append($dimension);
    }

    public function append($value): void {
        throw new \Exception('Use add method to append an dimension object.');
    }
    
   }
