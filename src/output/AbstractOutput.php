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

/**
 * Abstract class for output handlers
 */
abstract class AbstractOutput
{

     /* @var $actionKeyMapper indexed by action name to map action with band. */
    public array $actionKeyMapper = [
        'init' => 0,
        'totalHeader' => 0,
        'beforeGroup' => 0,
        'groupHeader' => 0,
        'detailHeader' => 0,
        'detailFooter' => 0,
        'detail' => 0,
        'groupFooter' => 0,
        'afterGroup' => 0,
        'totalFooter' => 0,
        'close' => 0,
        'noData' => 0,
        'noData_n' => 0,
        'detail_n' => 0,
        'noGroupChange_n' => 0,
    ];

    public function __construct(public string $separator = '') {

    }

    public function setSeparator(string $separator) {
        $this->separator = $separator;
    }

    abstract public function write($value, int $level, int $subKey);

    abstract public function prepend($value, int $level, int $subKey);

    abstract public function get(int $level, int $subKey);

    abstract public function pop(int $level, int $subKey);

    abstract public function delete(int $level, int $subKey);

    abstract public function __toString();
}
