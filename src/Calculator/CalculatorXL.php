<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */
declare(strict_types=1);

namespace gpoehl\phpReport\Calculator;

/**
 * Calculator with maximum functionality.
 * Next to the parent class also min and max values are identified.
 * Detection and maintaining min and max values comes with the cost of reduced
 * performance. When these are not needed use other calculator class.
 */
class CalculatorXL extends Calculator
{

    use MinMaxTrait;

    public function initialize(\Closure $getLevel, int $maxLevel): void {
        parent::initialize($getLevel, $maxLevel);
        $this->initializeMinMax($maxLevel);
    }

    public function add(int|float|string|null $value): void {
        parent::add($value);
        // set also min and max values.
        $this->setMinMax($value);
    }

    public function sub(int|float|string|null $value): void {
        parent::sub($value);
        // set also min and max values.
        $this->setMinMax($value * -1);
    }

    public function cumulateToNextLevel(int $level): void {
            parent::cumulateToNextLevel($level);
            // evaluate also min and max values.
            $this->cumulateMinMaxToNextLevel($level);
    }

}
