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

/*
 * Methods for counts and averages
 */

Trait CounterTrait
{

    /** @var int[] Counter which holds the number of added values.
     * Note: The value of an counter within a sheet is usually less than the
     * corresponding row counter. The sum() of all calculators equals the
     * sum() of the row counter.
     *
     * Note: The value of this counter doesn't equal the value of the row counter
     * when the add() method isn't called for each data row once.
     */
    protected array $counter = [];

    /** @var int[] not null counter. Number of values with values not equal to Null. */
    protected array $nnCounter = [];

    /** @var int[] not zero counter. Number of values with values not equal to zero */
    protected array $nzCounter = [];

    public function initializeCounter() {
        $this->counter = $this->nzCounter = $this->nnCounter = array_fill(0, $this->maxLevel + 1, 0);
    }

    /**
     * Get the number of calculated values.
     * @param $level The requested level. Defaults to the current level.
     * @return The total number of calculated values for the requested level.
     */
    public function count(int|string|null $level = null): int {
        return array_sum(array_slice($this->counter, ($this->getLevel)($level)));
    }

    /**
     * Get the number of calculated values beeing not null.
     * @param $level The requested level. Defaults to the current level.
     * @return The total number of calculated not null values for the requested level.
     */
    public function countNN(int|string|null $level = null): int {
        return array_sum(array_slice($this->nnCounter, ($this->getLevel)($level)));
    }

    /**
     * Get the number of calculated values beeing not zero and not null.
     * @param $level The requested level. Defaults to the current level.
     * @return The total number of calculated not null and not zero values for
     * the requested level.
     */
    public function countNZ(int|string|null $level = null): int {
        return array_sum(array_slice($this->nzCounter, ($this->getLevel)($level)));
    }

    /**
     * Get the average of calculated values.
     * @param $level The requested level. Defaults to the current level.
     * @return The average of calculated values for the requested level.
     */
    public function avg(int|string|null $level = null): int|float|string|null {
        return $this->getAvg($level, $this->counter);
    }

    /**
     * Get the average of calculated values beeing not null.
     * @param $level The requested level. Defaults to the current level.
     * @return The average of calculated values beeing not null for the requested level.
     */
    public function avgNN(int|string|null $level = null): int|float|string|null {
        return $this->getAvg($level, $this->nnCounter);
    }

    /**
     * Get the average of calculated values beeing not null and not zero.
     * @param $level The requested level. Defaults to the current level.
     * @return The average of calculated values beeing not null and not zero for the requested level.
     */
    public function avgNZ(int|string|null $level = null): int|float|string|null {
        return $this->getAvg($level, $this->nzCounter);
    }

    /**
     * Get the average of calculated values.
     * @param $level The requested level. Defaults to the current level.
     * @param $counter The requested counter to calculate the requested average.
     * @return The calculated average of added values for the requested level or
     * null when the counter is zero.
     */
    abstract protected function getAvg(int|string|null $level, array $counter): int|float|string|null;
}
