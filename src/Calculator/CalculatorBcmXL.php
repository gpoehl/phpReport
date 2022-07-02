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
class CalculatorBcmXL extends CalculatorBcm
{

    use MinMaxTrait;

    public function initialize(\Closure $getLevel, int $maxLevel): void {
        parent::initialize($getLevel, $maxLevel);
        $this->initializeMinMax($maxLevel);
    }

    public function add(int|float|string|null $value): void {
        parent::add($value);
        $this->setMinMax(bcadd('0', (string) $value, $this->scale));
    }

    public function sub(int|float|string|null $value): void {
        parent::sub($value);
        $this->setMinMax(bcmul((string) $value, '-1', $this->scale));
    }

    public function cumulateToNextLevel(int $level): void {
        if ($level > $this->maxLevel) {
            return;
        }
        parent::cumulateToNextLevel($level);
        $this->cumulateMinMaxToNextLevel($level);
    }

}
