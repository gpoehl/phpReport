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

namespace gpoehl\phpReport\Output;

/**
 * Abstract class for output handlers
 */
abstract class AbstractOutput
{

//     /* @var $actionKeyMapper indexed by action name to map action with band. */
 public \WeakMap $actionKeyMapper;
//    public array $actionKeyMapper = [
//         'Start' => 0,
//        'TotalHeader' => 0,
//        'GroupFirst' => 0,
//        'GroupBefore' => 0,
//        'GroupHeader' => 0,
//        'DetailHeader' => 0,
//        'Detail' => 0,
//        'DetailFooter' => 0,
//        'GroupFooter' => 0,
//        'GroupLast' => 0,
//        'GroupAfter' => 0,
//        'TotalFooter' => 0,
//        'Finish' => 0,
//        'NoData' => 0,
//        'DimNoData' => 0,
//        'DimDetail' => 0,
//        'DimNoGroupChange' => 0,
//    ];

    abstract function __construct(string $separator);   

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
