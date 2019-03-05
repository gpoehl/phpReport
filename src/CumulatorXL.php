<?php

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Cumulator to summarize attribute
 * This cumulator offers maximum functionality. On top of Cumulator also min()
 * and max() values are identified.
 * Detection and maintaining min and max values comes with the cost of reduced
 * performance. When min or max value is not needed use other Cumulator class. 
 * @author Günter
 */
class CumulatorXL extends Cumulator implements MinMaxInterface {

    private $min = [];      // Minimum value per level. Key is level
    private $max = [];      // Maximum value per level. Key is level

    public function __construct(MajorProperties $mp, int $maxLevel) {
        parent::__construct($mp, $maxLevel);
        // Initialize arrays for cumulated values, counters and min / max only on $maxLevel
        $this->initializeValue(0, $maxLevel);
    }

    /**
     * Add value to $maxLevel.
     * @return void
     */
    public function add($value): void {
        parent::add($value);
        if ($this->max[$this->maxLevel] === null) {
            $this->min[$this->maxLevel] = $value;
            $this->max[$this->maxLevel] = $value;
        } elseif ($value > $this->max[$this->maxLevel]) {
            $this->max[$this->maxLevel] = $value;
            // When value is greater max value it can't be less min.
        } elseIf ($value !== null && $value < $this->min[$this->maxLevel]) {
            $this->min[$this->maxLevel] = $value;
        }
    }

    protected function initializeValue($value, int $level): void {
        $this->total[$level] = $value;
        $this->nz[$level] = $this->nn[$level] = 0;
        $this->min[$level] = $this->max[$level] = null;
    }

    /**
     * Cumulate attribute values to higher level
     */
    public function cumulateToNextLevel(): void {
        $level = $this->mp->level;
        if ($level > $this->maxLevel) {
            return;
        }
        $next = $level - 1;
        if (isset($this->total[$next])) {
            $this->total[$next] += $this->total[$level];
            $this->nn[$next] += $this->nn[$level];
            $this->nz[$next] += $this->nz[$level];
            If ($this->min[$level] < $this->min[$next]) {
                $this->min[$next] = $this->min[$level];
            }
            if ($this->max[$level] > $this->max[$next]) {
                $this->max[$next] = $this->max[$level];
            }
        } else {
            $this->total[$next] = $this->total[$level];
            $this->nn[$next] = $this->nn[$level];
            $this->nz[$next] = $this->nz[$level];
            $this->min[$next] = $this->min[$level];
            $this->max[$next] = $this->max[$level];
        }

        if ($level !== $this->maxLevel) {
            unset($this->total[$level], $this->nn[$level], $this->nz[$level],
                    $this->min[$level], $this->min[$level]);
        } else {
            $this->initializeValue(0, $this->maxLevel);
        }
    }

    /**
     * Calculate the running sum up to the requested level.
     * @param int|null $level The requested level. Defaults to the current level
     * @return numeric The running total of added values from the requested level down
     * to the lowest level
     */
    public function min(int $level = null) {
        $min = null;
        for ($i = $level ?? $this->mp->level; $i <= $this->maxLevel; $i++) {
            if (isset($this->min[$i]) && ($this->min[$i] < $min || $min === null )) {
                $min = $this->min[$i];
            }
        }
        return $min;
    }

    public function max(int $level = null) {
        $max = null;
        for ($i = $level ?? $this->mp->level; $i <= $this->maxLevel; $i++) {
            if (isset($this->max[$i]) && ($this->max[$i] > $max )) {
                $max = $this->max[$i];
            }
        }
        return $max;
    }

}
