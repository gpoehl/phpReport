<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

namespace gpoehl\phpReport;

/**
 * Runtime exception for not existing properties.
 */
class UnknownPropertyException extends \RuntimeException {

    /**
     * @return the user-friendly name of this exception
     */
    public function getName() :string {
        return 'Unknown Property';
    }
}
