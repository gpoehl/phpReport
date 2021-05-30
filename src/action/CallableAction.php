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

namespace gpoehl\phpReport\action;

/**
 * Execute callable, closure or method
 */
class CallableAction {
   
     public function __construct(public $action) {
        ;
    }
    
    public function execute(... $params) {
        return ($this->action)(... $params);
    }
}
