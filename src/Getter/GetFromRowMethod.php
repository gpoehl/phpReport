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
 * Gets value from method where the data row is the object.
 */
class GetFromRowMethod extends BaseGetter
{

    /**
     * @see BaseGetter::getValue
     */
    public function getValue($row, $rowKey = null) {
        return $row->{$this->source}(... $this->params);
    }

}
