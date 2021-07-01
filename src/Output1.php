<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright © Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Basic Class to collect to handle output from actions
 */
class Output1
{

    private array $header =[];
    private array $detail =[];
    private array $footer =[];

    /**
     * @param MajorPropertiesService $mp Object of major properties  
     * @param int $maxLevel The maximum (group) level 
     * Initialize all levels with 0 values
     */
    public function __construct(private int $maxLevel, private string $separator='') {
    }

   
    
     public function writeHeader(int $level, $value) {
        $this->header[$level][] = $value;
    }
    
    public function writeDetail(int $level, $value) {
        $this->detail[$level][] = $value;
    }
    
    public function writeFooter(int $level, $value) {
        $this->footer[$level][] = $value;
    }


    public function get(?int $level = 0) {
        $level ??= 0;
        $wrk = [];
        // Headers from requested level to maxLevel (top down)
        for ($i = $level; $i <= $this->maxLevel; $i++) {
            if (isset($this->header[$i])) {
                $wrk[] = implode($this->separator, $this->header[$i]);
            }
        }
        // From maxLevel to requested level (bottom up)
        for ($i = $this->maxLevel; $i >= $level; $i--) {
            // Detail
            if (isset($this->detail[$i])) {
                $wrk[] = implode($this->separator, $this->detail[$i]);
            }
            // Footer
            if (isset($this->footer[$i])) {
                $wrk[] = implode($this->separator, $this->footer[$i]);
            }
        }
        return implode($this->separator, $wrk);
    }

    public function pop(?int $level) {
        return array_pop($this->output[$level - 1]);
    }

    public function cumulateToNextLevel($level): void {
        $wrk = [];
        if (isset($this->header[$level])) {
            $wrk[] = implode($this->separator, $this->header[$level]);
            unset ($this->header[$level]);
        }
        if (isset($this->detail[$level])) {
            $wrk[] = implode($this->separator, $this->detail[$level]);
            unset ($this->detail[$level]);
        }
        if (isset($this->footer[$level])) {
            $wrk[] = implode($this->separator, $this->footer[$level]);
            unset ($this->footer[$level]);
        }
        if (!empty($wrk)){
            $this->detail[$level - 1][] = implode($this->separator, $wrk);
        }
    }

}
