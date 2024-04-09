<?php

declare(strict_types=1);
/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

namespace gpoehl\phpReport;

/**
 * Alternate prototype class.
 * Returns minmum string output primarily for running unit tests.
 * 
 * Actions to be executed in default target might be redirected to a prototype 
 * class. See setting runTimeOptions.
 * 
 * Methods might also be called from default actions by calling the prototype()
 * method of the report class.
 */
class PrototypeMini extends PrototypeBase {

    /**
     * 
     * @var $printArguments Print json encoded parameter after method name when true.
     */
    public bool $printArguments = false;

    public function __call($name, $arguments) {
        $out = $this->getMethodName();
        if ($this->printArguments && $arguments !== []) {
            $out .= json_encode($arguments);
        }
        return "$out, ";
    }
}
