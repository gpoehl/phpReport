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
 * Interface to get value(s) related to a data row.
 */
interface GetValueInterface
{

    /**
     * Get the value declared by $source out of $row.
     * @var mixed $row The current data row.
     * @var mixed|null $rowKey The key of the current data row.
     * @return mixed The desired data value.
     */
    public function getValue($row, $rowKey = null);
}
