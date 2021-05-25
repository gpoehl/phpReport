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
 * Implement calculator nn (not null) and nz (not zero)  methods
 */
interface NnAndNzCounterIF {
   public function nn(int $level = null) : int;
   public function nz(int $level = null) : int;
}
