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

namespace gpoehl\phpReport;

/**
 * Class Dimension holds data per dimension
 */
class Dimension {

    public $id;         // The current dimension ID 
    public $nextID;     // The id of next dimension (just to avoid additions)
    public bool $isLastDim = true;  // Bool is this is the last dimension
    public bool $hasGroups = false;
    
    public $fromLevel;  // Level of first group within dimension
    public $lastLevel;  // Level of last group within dimension
    public $defaultTarget;     // Default class for user methods called in dataHandler
    public $total;
    public Action $noDataAction;
    public Action $noGroupChangeAction;
    public Action $detailAction;
    public $row;           // Current data row
    public $rowKey;        // Key of current data row
    public $groupValues = [];   // Array of group values to detect group change
    public $groupNames = [];     // Array of group names. Not indexed 
    public $dataHandler;        // Object which handles methods related to the type of data row.
    public array $groupSources = [];
    public array $groupGetters =[];
    public array $calcSources = [];
    public array $sheetSources = [];
    public array $calcGetters = [];
    public array $joinSource;
    public getter\BaseGetter $joinGetter;

    public function __construct(int $id, int $fromLevel, $defaultTarget, $total) {
        $this->id = $id;
        $this->nextID = ++$id;
        $this->fromLevel = $this->lastLevel = $fromLevel;
        $this->defaultTarget = $defaultTarget;
        $this->total = $total;
    }

    public function addGroupSource($value, $params) {
        $this->hasGroups = true;
        $this->lastLevel++;
        $this->groupSources [$this->lastLevel] = [$value, $params];
    }

    public function addCalcSource($name, $value, $params) {
        $this->calcSources [$name] = [$value, $params];
    }

    public function addSheetSource($name, $key, $value, $params) {
        $this->sheetSources [$name] = [$key, $value, $params];
    }

    public function setJoinSource($value, $params) {
        $this->joinSource = [$value, $params];
    }

    public function getGroupValues($row, $rowKey): array {
        (!isset($this->groupSources)) ?: $this->setGetters($row);
        $values = [];
        foreach ($this->groupGetters as $level => $getter) {
            $values [$level] = $getter->getValue($row, $rowKey);
        }
        return $values;
    }

    public function addValues(): void {
        foreach ($this->calcGetters as $name => $getter) {
            $this->total[$name]->add($getter->getValue($this->row, $this->rowKey));
        }
    }

    public function getJoinedData() {
        return $this->joinGetter->getValue($this->row, $this->rowKey);
    }

    /**
     * Save given parameters to make them active. 
     * Call method when footer actions are done and new 
     * row is active 
     * @param type $row
     * @param type $rowKey
     * @param array $groupValues
     */
    public function activateValues($row, $rowKey, array $groupValues): void {
        $this->row = $row;
        $this->rowKey = $rowKey;
        $this->groupValues = $groupValues;
    }

    private function setGetters($row) {
        $factory = new getter\GetterFactory(is_object($row));
        foreach ($this->groupSources as $level => $source) {
            $this->groupGetters[$level] = $factory->getGetter($source[0], $source[1]);
        }
        foreach ($this->calcSources as $name => $source) {
            $this->calcGetters[$name] = $factory->getGetter($source[0], $source[1]);
        }
        foreach ($this->sheetSources as $name => $source) {
            $this->calcGetters[$name] = $factory->getSheetGetter($source[0], $source[1], $source[2]);
        }
        if (!$this->isLastDim) {
            $this->joinGetter = $factory->getGetter($this->joinSource[0], $this->joinSource[0]);
        }
        unset($this->groupSources, $this->calcSources, $this->sheetSources, $this->joinSource);
    }

}
