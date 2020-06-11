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
 * BaseGetter is the base class for all getter classes
 */
abstract class BaseGetter {

    /**
     * @var mixed The source (attribute name, array key or method / closure) where
     * to find the desired value in a data row. 
     * For sheets $source is an array having a source for the sheet key and value.
     */
    protected $source;

    /** @var array|empty Variadic list of optional parameters passed to closures and methods. */
    protected $params;

    public function __construct($source, $params) {
        $this->source = $source;
        $this->params = $params;
    }

    /**
     * Get the value declared by $source out of $row.
     * @var mixed $row The data row wich holds the value.
     * @var int|string|null $rowKey The key of the data row. Can be used to build
     * the value to be returned.
     * @return mixed The desired data value (by $source) from $row.
     */
    public abstract function getValue($row, $rowKey =null);
}
