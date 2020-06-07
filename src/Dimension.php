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
 * Dimension holds related data per data dimension.
 * Each report has at least one dimension. Addional ones are instantiated
 * for every joined data. 
 */
class Dimension {

    /** @var The current dimension id. First dimension has id = 0. */
    public int $id;

    /** @var True when this is the last dimension (has not joined data). */
    public bool $isLastDim = true;

    /** @var Level of first group assigned to this dimension. */
    public int $fromLevel;

    /** @var Level of last group assigned to this dimension. Same as $fromLevel
     * when no group is assigned. */
    public $lastLevel;

    /** @var Default class or object for getters using methods. */
    private $defaultTarget;

    /** @var Reference to Collector Report->total object from Report.
     * Used to addValues() without returning values to Report object. */
    private Collector $total;

    // Actions to be executed when dimension is not the last one.

    /** @var Action object to be executed when join returns no data. */
    public Action $noDataAction;

    /** @var Action object to be executed when row doesn't trigger a group change. */
    public Action $noGroupChangeAction;

    /** @var Action object to be executed for every data row. */
    public Action $detailAction;

    /** @var The current active data row. */
    public $row;

    /** @var The key of the current active data row. */
    public $rowKey;

    /** @var Groups[] Groups declared for this dimension. */
    public array $groups = [];

    /** @var Array of active group values indexed by group level (after executing footers. */
    public array $groupValues = [];

    /** @var Indicator if row type is detected and getter ojects are instantiated. */
    private bool $gettersSet = false;

    /** @var BaseGetter[] Object to get group value indexed by group level. */
    public array $groupGetters = [];

    /** @var BaseGetter[] Object to get values for compute items and sheets indexed by name. */
    public array $calcGetters = [];
    
    /** @var Object to get joined values. */
     public getter\BaseGetter $joinGetter;
    
    // Source values will be unset after instantiating of getter objects. 
    /** @var array[] Parameter for compute items indexed by name. */ 
    private array $calcSources = [];
    /** @var array[] Parameter for sheet items indexed by name. Will also be handled by calcGetters. */ 
    private array $sheetSources = [];
    /** @var Parameter for joined data. */ 
    private array $joinSource;

    /**
     * Instantiate a new dimension object
     * @param int $id The dimension id
     * @param int $fromLevel The lowest group level assigned to this dimension
     * @param type $defaultTarget The target class / object from report
     * @param \gpoehl\phpReport\Collector $total The 'total' collector from report class 
     */
    public function __construct(int $id, int $fromLevel, $defaultTarget, Collector $total) {
        $this->id = $id;
        $this->fromLevel = $this->lastLevel = $fromLevel;
        $this->defaultTarget = $defaultTarget;
        $this->total = $total;
    }

    /**
     * Keep parameters for an computed item until getter class is instantiated.
     * @param string $name
     * @param mixed $value Source of the value to be computed
     * @param array|empty $params Additional variadic parameters passed when  
     * $source is a callable.
     */
    public function addCalcSource(string $name, $value, $params) {
        $this->calcSources [$name] = [$value, $params];
    }

    /**
     * Keep parameters for a sheet until getter class is instantiated.
     * @param string $name
     * @param mixed $key Source of the key for the sheet
     * @param mixed $value Source of the value for the sheet
     * @param array|empty $params Additional variadic parameters passed when  
     * $source is a callable.
     */
    public function addSheetSource(string $name, $key, $value, $params) {
        $this->sheetSources [$name] = [$key, $value, $params];
    }

     /**
     * Keep parameters for joined data until getter class is instantiated.
     * @param mixed $value Source of the joined data
     * @param array|empty $params Additional variadic parameters passed when  
     * $source is a callable.
     */
    public function setJoinSource($value, $params) {
        $this->joinSource = [$value, $params];
    }

    /**
     * Get all group values from given row.
     * @param type $row
     * @param type $rowKey
     * @return array
     */
    public function getGroupValues($row, $rowKey): array {
        ($this->gettersSet) ?: $this->setGetters($row);
        $values = [];
        foreach ($this->groupGetters as $level => $getter) {
            $values [$level] = $getter->getValue($row, $rowKey);
        }
        return $values;
    }

    /**
     * Add values for computed items and sheets to 'total' collector
     */
    public function addValues(): void {
        foreach ($this->calcGetters as $name => $getter) {
            $this->total[$name]->add($getter->getValue($this->row, $this->rowKey));
        }
    }

    /**
     * Get data joined to the current row
    */
    public function getJoinedData() {
        return $this->joinGetter->getValue($this->row, $this->rowKey);
    }

    /**
     * Save given parameters to make them active. 
     * Call method when footer actions are done and new row is active. 
     * @param type $row
     * @param type $rowKey
     * @param array $groupValues
     */
    public function activateValues($row, $rowKey, array $groupValues): void {
        $this->row = $row;
        $this->rowKey = $rowKey;
        $this->groupValues = $groupValues;
    }

    /**
     * Detect row type and let GetterFactory instantiate getter objects for
     * @param type $row
     */
    private function setGetters($row) {
        $this->gettersSet = true;
        $factory = new getter\GetterFactory(is_object($row));
        foreach ($this->groups as $group) {
            $this->groupGetters[$group->level] = $factory->getGetter($group->valueSource, $group->params);
            unset($group->valueSource, $group->params);
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
        unset($this->calcSources, $this->sheetSources, $this->joinSource);
    }

}
