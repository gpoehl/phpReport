<?php

/*
 * This file is part of the gpoehl/vcard library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link      https://github.com/gpoehl/vard/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Implement calculator min and max methods
 */
interface MinMaxIF {
   public function min(int $level = null);
   public function max(int $level = null);
}
