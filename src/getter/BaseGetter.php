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
abstract class BaseGetter implements GetValueInterface
{

    /**
     * @var mixed $source The source (attribute name, array key or method / closure) where
     * to find the desired value in a data row.
     * For sheets $source is an array having a source for the sheet key and value.
     *
     * @var array $params Optional parameters passed unpacked to closures and methods.
     */
    public function __construct(protected $source, protected ?array $params = null) {

    }

}
