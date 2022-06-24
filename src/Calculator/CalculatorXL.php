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

    /**
     * Don't call this method yourself. The report class takes care for calling.
     */
    public function initialize(\Closure $getLevel, int $maxLevel) {
        parent::initialize($getLevel, $maxLevel);
        $this->initializeMinMax($maxLevel);
    }

    /**
     * Add value at $maxLevel, increment counters and set min and max values.
     * Min and max values are set when value is not null.
     * @param $value The data value which will be added
     */
    public function add(int|float|string|null $value): void {
        parent::add($value);
        $this->setMinMax($value);
    }

    /**
     * Subtract given value at $maxLevel, increment counters and set min and max values.
     * When value is not null min and max values are set with the negated value
     * @param $value The data value which will be subtracted
     */
    public function sub(int|float|string|null $value): void {
        parent::sub($value);
        $this->setMinMax($value * -1);
    }

    /**
     * Cumulate values to next higher level and reset values on given level.
     * Add values and counters from the current level to the next higher level.
     * Min and max values will be evaluated.
     */
    public function cumulateToNextLevel(int $level): void {
        if ($level > $this->maxLevel) {
            return;
        }
        parent::cumulateToNextLevel($level);
        $this->cumulateMinMaxToNextLevel($level);
    }

}
