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
 * Gets key and data value for sheets.
 * Note: $source is an array where element 0 has a getter for the key and
 * element 1 the getter for the data value.
 */
class GetForSheet extends BaseGetter {

    /**
     * @see getValue()
     * @return array Associated array where the key represents the column in an sheet
     */
    public function getValue($row, $rowKey) :array {
        return [$this->source[0]->getValue($row, $rowKey) => $this->source[0]->getValue($row, $rowKey)];
    }

}
