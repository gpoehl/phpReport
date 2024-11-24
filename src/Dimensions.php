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

   
    public function __construct($array = []) {
        // Call push method to make sure we got an Dimension object.
        foreach ($array as $value){
            $this->push($value);
        }
        
    }

    #[\Override]
    public function current(): Dimension {
        return parent::current();
    }

    #[\Override]
    public function offsetGet($offset): Dimension {
        return parent::offsetGet($offset);
    }

    /**
     * Add a dimension object
     * @throws InvalidArgumentException when dimension name exists
     */
    public function push(Dimension $dimension): void {
        if (!empty($this->names)) {
            if (array_key_exists($dimension->name, $this->names)) {
                throw new \Exception("Dimension name '{$dimension->name}' already exists.");
            }
            $currentDim = $this->current();
            $dimension->lastLevel = $currentDim->lastLevel;
            $dimension->id = $this->count();
        }
        $this->names[$dimension->name] = $dimension->id;
        parent::append($dimension);
    }

    #[\Override]
    public function append($dimension): void {
        // Call add method to make sure we got an Dimension object.
        $this->push($dimension);
    }
}
