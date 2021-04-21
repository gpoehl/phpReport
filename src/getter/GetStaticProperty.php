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
 * Gets value from an static property. Not related to a data row.
 * Class can be a class name or an object.
 */
class GetStaticProperty extends BaseGetter{
   
    /**
     * @see BaseGetter::getValue
     */
    public function getValue($row, $rowKey = null) {
        // Note the ${$name[1]} syntax!
        return $this->source[0]::${$this->source[1]};
    }
}
