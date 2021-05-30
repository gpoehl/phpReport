<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright Günter Pöhl
 * @link https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport\action;

/**
 * Raise notice, warning or error
 */
class ErrorExecuter
{

    public function __construct(private int $kind) {
        
    }

    public function execute($message) {
        trigger_error($message, $this->kind);
    }

}
