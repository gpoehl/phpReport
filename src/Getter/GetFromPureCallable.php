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

namespace gpoehl\phpReport\Getter;

/**
 * $sour
 * Gets value from a callable without passing row and rowkey.
 * That allows calling methods independend from a data row.
 */
class GetFromPureCallable extends BaseGetter
{

    /**
     * @see BaseGetter::getValue
     * @var mixed $source Closure or array with object and object member name
     */
    public function getValue($row, $rowKey = null) {
        return ($this->source)(...$this->params);
    }

}
