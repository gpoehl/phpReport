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

use gpoehl\phpReport\getter\BaseGetter;
use gpoehl\phpReport\getter\GetterFactory;

/**
 * Dimension holds related data per data dimension.
 * Each report has at least one dimension. Addional ones are instantiated
 * for every joined data.
 */
class Dimension {

    /** @var True when this is the last dimension (has not joined data). */
    public bool $isLastDim = true;
    // Actions to be executed when dimension is not the last one.

    /* @var $actions Action objects indexed by ActionKey enum. */
    public \WeakMap $actions;

    /** @var The current active data row. */
    public $row;

    /** @var The key of the current active data row. */
    public $rowKey;

    /** @var Groups[] Groups declared for this dimension. */
    public array $groups = [];

    /** @var Array of active group values indexed by group level (after executing footers. */
    public array $groupValues = [];

    /** @var Indicator if row type is detected and getter ojects are instantiated. */
    private bool $gettersInstantiated = false;

    /** @var BaseGetter[] Object to get group value indexed by group level. */
    private array $groupGetters = [];

    /** @var BaseGetter[] Object to get values for compute items and sheets indexed by name. */
    public array $calcGetters = [];

    /** @var Object to get joined values. */
    public BaseGetter $joinGetter;

    // Source values will be unset after instantiating of getter objects.

    /** @var array[] Parameter for compute items indexed by name. */
    private array $calcSources = [];

    /** @var array[] Parameter for sheet items indexed by name. Will also be handled by calcGetters. */
    private array $sheetSources = [];

    /** @var Parameter for joined data. */
    public array $joinSource;

    /**
     * Instantiate a new dimension object
     * @param int $id The dimension id. First dimension has id = 0.
     * @param int $lastLevel  Level of last group assigned to this dimension.
     * Same as $lastLevel from previous dimension when no group is assigned.
     * @param object|className $defaultTarget Object or name of a class in which the
     * methods will be called when $source is not specified.
     */
    public function __construct(public int $id, public string $name, public int $lastLevel, private $defaultTarget) {
        $this->actions = new \WeakMap();
    }

    /**
     * Add a group to groups array
     * @param \gpoehl\phpReport\Group $group The group object
     */
    public function addGroup(Group $group): void {
        $this->groups[] = $group;
        $this->lastLevel = $group->level;
    }

    /**
     * Keep parameters for computed items until getter class is instantiated.
     * @param string $name
     * @param mixed $source Source of the value to be computed
     * @param array $params Parameters passed unpacked when $source is a callable.
     */
    public function setCalcSource(string $name, $source, array $params): void {
        $this->calcSources [$name] = [$source, $params];
    }

    /**
     * Keep parameters for a sheet until getter class is instantiated.
     * @param string $name
     * @param mixed $keySource Source of the key for the sheet
     * @param mixed $source Source of the value for the sheet
     * @param array $keyParams Parameters passed unpacked when $keySource is a callable.
     * @param array $params Parameters passed unpacked when $source is a callable.
     */
    public function setSheetSource(string $name, $keySource, $source, array $keyParams, array $params): void {
        $this->sheetSources [$name] = [$keySource, $source, $keyParams, $params];
    }

    /**
     * Keep parameters for joined data until getter class is instantiated.
     * @param mixed $source Source of the joined data
     * @param array $params Parameters passed unpacked when $source is a callable.
     */
    public function setJoinSource($source, array $params): void {
        $this->isLastDim = false;
        $this->joinSource = [$source, $params];
    }

    /**
     * Get all group values from given row.
     * This is the first method call for each data row. So this is the place to
     * instantiate getters.
     * @param type $row
     * @param type $rowKey
     * @return array The requested group values indexed by group level.
     */
    public function getGroupValues($row, $rowKey = null): array {
        if (!$this->gettersInstantiated) {
            $this->instantiateGetters($row);
            $this->gettersInstantiated = true;
        }
        $values = [];
        foreach ($this->groupGetters as $level => $getter) {
            $values [$level] = $getter->getValue($row, $rowKey);
        }
        return $values;
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
    public function activateValues($row, $rowKey = null, array $groupValues = []): void {
        $this->row = $row;
        $this->rowKey = $rowKey;
        $this->groupValues = $groupValues;
    }

    /**
     * Set getter objects on first request for group values
     * Detect row type and let GetterFactory instantiate all required getter objects
     */
    private function instantiateGetters($row): void {
        $factory = new GetterFactory($row, $this->defaultTarget);
        foreach ($this->groups as $group) {
            $this->groupGetters[$group->level] = $factory->getGetter($group->valueSource, $group->params);
            unset($group->valueSource, $group->params);
        }
        foreach ($this->calcSources as $name => $source) {
            $this->calcGetters[$name] = $factory->getGetter(... $source);
        }
        foreach ($this->sheetSources as $name => $source) {
            $this->calcGetters[$name] = $factory->getSheetGetter(... $source);
        }
        if (!$this->isLastDim) {
            $this->joinGetter = $factory->getGetter(... $this->joinSource);
//            echo '<br>Dim ' . $this->id .'<br>';
//                var_dump($this->joinGetter);
        }
        unset($this->calcSources, $this->sheetSources, $this->joinSource);
    }
}
