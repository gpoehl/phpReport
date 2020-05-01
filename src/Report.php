<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Main class of phpReport.
 * Accepts any data input and monitors values in defined group attributes
 * between two data rows. When given values are not equal actions for group
 * headers and group footers will be executed.
 * 
 * This class offers also differnt ways to calulate the (running) sum of any attribute.
 * Row counters and group counters are always active and can be used at any time.
 */
class Report {

    const VERSION = '2.1.0';
    // Rules to execute actions
    const CALL_EXISTING = 0;          // Call methods in owner class only when implemented. Default.
    const CALL_ALWAYS = 1;            // Call also not existing methods in owner class. Allows using magic function calls.
    const CALL_PROTOTYPE = 2;         // Call methods in prototype class when not implemented in owner class.
    const CALL_ALWAYS_PROTOTYPE = 3;  // Call methods in prototype class for any action.
    // Cumulator class selection
    const XS = 1;                       // CumulatorXS class (default)
    const REGULAR = 2;                  // Cumulator class (has not null and not zero counters)
    const XL = 3;                       // CumulatorXL class (has also min and max values) 
    // Action types are used internally to speed up action execution. 
    const STRING = 1;
    const CLOSURE = 2;
    const CALLABLE = 3;
    const METHOD = 4;
    const WARNING = 5;
    const ERROR = 6;

    /** Collected return values from executed actions. */
    public ?string $output = null;

    /** @var Major properties to be passed to calculator objects. */
    public MajorProperties $mp;

    /** @var Collection of row counters. One row counter per dimension, */
    public Collector $rc;

    /** @var Collection of group counters. One group counter per group. */
    public Collector $gc;

    /** @var Collection of aggregate values, sheets and other collectors */
    public Collector $total;

    /** @var Arrray of the varadic parameters given at instantiaton.
     * They can be used as an alternate way to pass parameters between
     * objects (e.g. from a controller to a report class). */
    public array $params;

    /** @var The currently executed action. */
    public Action $currentAction;

    /** @var array[] Array of arrays with possible actions loaded from config file
     * indexed by the action key. */
    private array $actions = [];

    /** @var The runTimeAction for 'detail' event. */
    private Action $detailAction;

    /** @var Dimension[] An arrray of data dimension objects. */
    private array $dims = [];

    /** @var The actual dimension object. Shortcut of current($dims). */
    private Dimension $dim;

    /** @var Highest level of changed group. Null when no group change detected. */
    private ?int $changedLevel;

    /** @var Groups object which holds any groups regardless of the related dimension. */
    private Groups $groups;

    /** @var object Object which implements methods for event actions.
     * Usally passed as $this.
     * @todo Check if it is possible to pass a class name and call static methods
     */
    private $target;

    /** @var The option how and where event actions will be executed. */
    private int $callOption = self::CALL_EXISTING;

    /** @var The prototype object which executes prototype actions. Will be 
      instantiated only when really needed. */

    private ?Prototype $prototype = null;

    /** mixed Rule how group names will be build. See configuration documentaion. */
    private $buildMethodsByGroupName;

    /** var Group level of lowest called header. This wil be the first footer.
     * The group level of the last dimension can't be used. When a dimension has
     * no data rows header for this (and following) dimension wasn't executed.
     */
    private int $lowestHeader = 0;

    /**
     * Instantiate a new report object
     * Set reference to a target object and merge config parameters into 
     * parameters from config file.
     * @param object $target Object which holds the methods to be called. Usually passed as $this.
     * @param array|null $config Dynamic configuration to replace defaults set in config.php file.
     * @param mixed ...$params Optional parameters to be passed around. They are
     * kept in the public $params array but not used by this library. 
     */
    public function __construct(object $target, array $config = null, ...$params) {
        $this->target = $target;
        $this->params = $params;
        $conf = Factory::configurator($config);
        $this->groups = new Groups($conf->grandTotalName);
        $this->buildMethodsByGroupName = $conf->buildMethodsByGroupName;
        $this->actions = $conf->actions;
        $this->mp = Factory::properties();
        $this->mp->rc = $this->rc = Factory::collector();
        $this->mp->gc = $this->gc = Factory::collector();
        $this->mp->total = $this->total = Factory::collector();
        return $this;
    }

    /**
     * Set data handler and optionally declare next data dimension.
     * For each data dimension this method should be called. It must be called
     * before your can declare groups or call the aggregate or sheet methods.
     * 
     * Next dimension can be next dimension in an multi dimensional array or
     * data of an 1:n relationship where the related data is not part of the
     * current dimension. 
     * 
     * @param mixed $dataHandler The data handler which gets data out of a data.
     * You can use 'array' as a shortcut to ArrayDataHandler or 'object' to
     * ArrayDataHandler.
     * If the last dimension don't has groups, aggregate or sheet declarations
     * a new dimension will be instantiated when you omit the data() method call. 
     * @param mixed $value Method, callable, closure or attribute name. 
     * Methods, callables and closures must return an iterable data set, null 
     * when no data exists. 
     * They can also pass data themselves to the run() or next() methods. In this
     * case they must return false.
     * To detect if $data is a method or callable it must be an array. If that arrary
     * has only one parameter a method with the name in parameter will be 
     * called in the owner class.
     * Attribute name is the name of an attribute when current row is an object
     * or the array key when current row is an array.
     * 
     * @param mixed $noDataAction Action to be executed when $data don't have any data.
     * Null to use defualt noData_n action from configuration.
     * @param mixed $dataAction Action to be executed for each data row of the current dimension.
     * Null to use default data_n action from configuration.
     * @param mixed noGroupChange Action to be executed when data row didn't
     * trigger a group change.
     * Null to use default noGroupChange_n action from configuration.
     * @param mixed ...$parameters Optional list of additional parameters passed 
     * to external methods. 
     */
    public function data($dataHandler, $value = null, $noDataAction = null, $dataAction = null, $noGroupChangeAction = null, ... $params): self {
        $dimID = count($this->dims);
        $this->dims[] = $this->dim = new Dimension($dimID, $dataHandler, $value, $this->target, $params);
        $this->dim->noDataAction = $this->makeAction('noData_n', $noDataAction, $dimID);
        $this->dim->noGroupChangeAction = $this->makeAction('noGroupChange_n', $noGroupChangeAction, $dimID);
        $this->dim->detailAction = $this->makeAction('detail_n', $dataAction, $dimID);
        return $this;
    }

    /**
     * Declare a new data group.
     * Call method once for each data group after calling the data() method.
     * Values will be compared between two consecutive data rows. When 
     * they aren't equal defined footer and header actions will be executed.
     *    
     * @param string $name The group name.
     * This name will be used to build method names (depending on configuration
     * parameters). Must be unique between all dimensions.
     * 
     * @param int|string|\Closure|array|null $value How to get the group value. 
     * It can be one of the following:
     * 
     * - Name or index of an array element when data row is an array.
     * - The object property name when data row is an object.
     * - A callback returning a value.
     * - A callable array (may contain an object or class name and must contain a method name) returning a value.
     * - Null. The group name will be used as array key or property name.
     *  
     *  * The signature of an anonymous function or callable method should be:
     * `function($row, $rowKey, $dimID, $param1, ..., $param9)`.
     * 
     * ```php
     * fn($user, $rowkey) => $user->firstName . ' ' . $user->lastName 
     * ```
     * 
     * The signature of an anonymous function or callable method is specified by
     * the datahander class of this dimension.
     * Same is true for the content of callables with an array. 
     * Both depends mainly if the data row is an data object or an array. It's 
     * obvious that an data object don't need the current row as a parameter while
     * the latter repquries it. 
     * 
     * @param mixed $headerAction Overwrite individual group header action. 
     * False when default header action should not be executed.
     * @param mixed $footerAction Overwrite individual group footer action. 
     * False when default footer action should not be executed.
     * @param mixed ...$params Optional list of parameters passed `unpacked`
     * to anonymous functions and callables getting the group value.
     */
    public function group($name, $value = null, $headerAction = null, $footerAction = null, ...$params): self {
        $this->checkThatDimIsDeclared('group', $name);
        $value ??= $name;
        $group = $this->groups->newGroup($name, $this->dim->id);
        If ($this->buildMethodsByGroupName === 'ucfirst') {
            $replacement = ucfirst($name);
        } else {
            $replacement = ($this->buildMethodsByGroupName) ? $name : (string) $group->level;
        }
        $group->headerAction = $this->makeAction('groupHeader', $headerAction, $replacement);
        $group->footerAction = $this->makeAction('groupFooter', $footerAction, $replacement);

        $this->dim->dataHandler->addGroup($value, $params);
        $this->gc->addItem(Factory::calculator($this->mp, $group->level - 1, self::XS), $group->level);
        return $this;
    }

    /**
     * Aggregate values.
     * Instantiate an calulator object to aggregate declared value and provide
     * aggregate functions.
     * Aggregation functions are available at each group level at any time.
     * @param string $name Unique name to reference a calculator object. The
     * reference will be hold in $this->total.
     * @param mixed $value Source of the value to be aggregated. It's the attribute name 
     * when data row is an object or the key name when data row is an array.
     * It's also possiblbe to use a closure which returns the value. 
     * When the $value parameter is null it defaults to the content of $name parameter.  
     * Use False when the value should not be aggregation automaticly. In this case
     * only the referece to the calculator object will be established. Use the
     * add() method of the $total collector or from the calculator to caluclate
     * values.  
     * @param int|null $typ The calculator type. 
     * Typ is used to choose between a calculator class. Options are XS, REGULAR
     * and XL. Defaults to XS.
     * @param int|null $maxLevel The group level at which the value will be 
     * added. Defaults to the maximum level of the dimension. Might be less when
     * aggregated data are only needed on higher levels.
     * @param mixed ...$params Optional list of parameters passed `unpacked`
     * to anonymous functions and callables getting value.
     */
    public function aggregate(string $name, $value = null, ?int $typ = self::XS, ?int $maxLevel = null, ...$params): self {
        $this->checkThatDimIsDeclared('aggregate', $name);
        $typ ??= self::XS;
        $value ??= $name;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $this->total->addItem(Factory::calculator($this->mp, $maxLevel, $typ), $name);
        ($value === false) ?: $this->dim->dataHandler->addCalcItem($name, $value, $params);
        return $this;
    }

    /**
     * Aggreate values in a sheet.
     * Sheet is a collection of calculators for a horizontal representation of a value.
     * Call this method once for each sheet. 
     * @param string $name Unique name to reference the sheet object. The
     * reference will be hold in $this->total.
     * 
     * @param mixed $value Source of the key and value to be aggregated. Must be
     * served in an array with only one entry were key is the array key and value
     * the value. 
     * 
     * Key and attribute name are attribute names when data row is an object or
     * or when row is an array the element keys.
     * It's also possiblbe to use a closure which returns an array [key => value].
     * 
     * False to just instantiate and reference the sheet. To execute the calculation
     * call the add() method of the sheet object. This is very useful when 
     * getting the key or value to be aggregated is complex and / or you need
     * these data on the detail level. 
     * 
     * @param int|null $typ The calculator type. 
     * Typ is used to choose between a calculator class. Options are XS, REGULAR
     * and XL. Defaults to XS. Typ belongs to all sheet items.
     * @param mixed $fromKey To use a fixed sheet declare the first 
     * calculator name. Pass an array when sheet names are not in an sequence.
     * Example ['young', 'mid-aged', 'old'l
     * Null for sheets where calculators are instantiated for each key value.
     * @param mixed $toKey The last calculator name for fixed sheet. FromKey 
     * will be icremented until $toKey is reached.
     * @param int|null $maxLevel The group level at which the value will be 
     * added. Defaults to the maximum level of the dimension. Might be less when
     * aggregated data are only needed on higher levels.
     * @param mixed ...$params Optional list of parameters passed `unpacked`
     * to anonymous functions and callables getting the sheet key =>value pair.
     */
    public function sheet(string $name, $value, ?int $typ = self::XS, $fromKey = null, $toKey = null, $maxLevel = null, ...$params): self {
        $this->checkThatDimIsDeclared('sheet', $name);
        $typ ??= self::XS;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $this->total->addItem(Factory::sheet($this->mp, $maxLevel, $typ, $fromKey, $toKey), $name);
        ($value === false) ?: $this->dim->dataHandler->addSheetItem($name, $value, $params);
        return $this;
    }

    /**
     * Check that dim is set correct when methods are called which relates to a dim.
     * If possible a dim wihout calling the data() method will be instantiated. 
     * @param string $func The method name where this method was called from
     * @param string $name The name given to group, aggregate or sheet method 
     * @throws \InvalidArgumentException
     */
    private function checkThatDimIsDeclared(string $func, string $name): void {
        if (!isset($this->dim)) {
            throw new \InvalidArgumentException("Before calling the $func() method for '$name' the data() method must be called.");
        }
    }

    /**
     * Verify that maxlevel of sheet is not above current maxLevel
     * @param int $maxLevel to be checked. Defaults to the current maxLevel.
     * @return int maxLevel
     * @throws InvalidArgumentException
     */
    private function checkMaxLevel(int $maxLevel = null): int {
        if ($maxLevel === null) {
            $maxLevel = $this->groups->maxLevel;
        } elseif ($maxLevel > $this->groups->maxLevel) {
            throw new InvalidArgumentException("MaxLevel $maxLevel must be equal or less maxLevel of dim({$this->groups->maxLevel}).");
        }
        return $maxLevel;
    }

    /**
     * Create and return a new action object.
     * When the given $actionParam is not null it will overwrite the actionParam
     * from the config file.
     * @param string $actionKey
     * @param array|null|false $actionParam
     * @param mixed $replacement
     * @return Action object.
     */
    private function makeAction(string $actionKey, $actionParam, $replacement) {
        $wrk = ($actionParam !== null) ? Helper::buildMethodAction($actionParam, $actionKey) :
                Helper::replacePercent($replacement, $this->actions[$actionKey]);
        return new Action($actionKey, $wrk);
    }

    /**
     * On first call of run() this method is called.
     * Set fromLevel and lastLevel on dims, instantiate row counter and
     * eventually call init and totalHeader methods.
     */
    private function finalInitializion(): void {
        $fromLevel = 0;

        // Create dummy dimension with array data provider when for last 
        // dimension data() was not called. This works only when the last
        // dimension need no calc, sheet or groups.
        if (empty($this->dims) || !end($this->dims)->isLastDim) {
            $this->dims[] = new Dimension(count($this->dims), 'array');
        }
        foreach ($this->dims as $dim) {
            $lastLevel = $dim->setFromAndLastLevel($fromLevel);
            $this->rc->addItem(Factory::calculator($this->mp, $lastLevel, self::XS, $dim->id));
            $fromLevel = $lastLevel;
        }
        $this->dim = reset($this->dims);
        $this->mp->gc->setMapper($this->groups->groupLevel);
        $this->mp->groupLevel = $this->groups->groupLevel;
        $this->mp->lastLevel = $dim->lastLevel;
        $this->executeAction('init');
        $this->executeAction('totalHeader');
        $this->detailAction = new Action('detail', $this->actions['detail']);
        unset($this->actions['init'], $this->actions['totalHeader'], $this->actions['groupHeader'], $this->actions['groupFooter'],
                $this->actions['noData_n'], $this->actions['data_n'], $this->actions['noGroupChange_n'], $this->actions['detail']);
        $this->setRunTimeActions();
        $this->currentAction = $this->detailAction;
    }

    /**
     * Get prototype data for the current method called in $target.
     * @return string A html formatted table with some information related to
     * the last executed action.
     */
    public function prototype(): string {
        $this->prototype ??= new Prototype($this);
        return $this->prototype->magic();
    }

    /**
     * Set call option.
     * Setting call option defines which action will be taken. Usally call option
     * will be set at program start but can also set or altered during program execution.
     * The call option influences which call action will be executed and if 
     * methods are called in the owner or the prototye object.
     * @param int $callOption One of the CALL_x constants.
     * @throws InvalidArgumentException
     */
    public function setCallOption(int $callOption): self {
        if ($callOption < 0 || $callOption > 3) {
            throw new \InvalidArgumentException('Invalid call option');
        }
        if ($callOption >= self::CALL_PROTOTYPE && !isset($this->prototype)) {
            $this->prototype = new Prototype($this);
        }
        $this->callOption = $callOption;
        // Rebuild runTimeActions only when finalInitialisation wasn't done already.  
        if (!isset($this->actions['init'])) {
            $this->setRunTimeActions();
        }
        return $this;
    }

    /**
     * Set runtime actions for all actions which might be executed more than once.
     */
    private function setRunTimeActions(): void {
        $this->setRuntimeAction($this->detailAction);
        foreach ($this->groups->items as $group) {
            $this->setRuntimeAction($group->headerAction);
            $this->setRuntimeAction($group->footerAction);
        }

        // Exclude last dimension. Has no data from data() method. 
        foreach ($this->dims as $dim) {
            if (!$dim->isLastDim) {
                $this->setRuntimeAction($dim->noDataAction);
                $this->setRuntimeAction($dim->noGroupChangeAction);
                $this->setRuntimeAction($dim->detailAction);
            }
        }
    }

    /**
     * Call setRunTimeAction method for a given action
     * @param Action $action The action object.
     */
    private function setRuntimeAction(Action $action): void {
        $action->setRunTimeAction($this->target, $this->prototype, $this->callOption);
    }

    /**
     * Execute actions which don't have parameters and are executed only once.
     * @param string $key The action key of $this->actions
     */
    private function executeAction(string $key): void {
        $this->currentAction = new Action($key, $this->actions[$key]);
        $this->currentAction->setRunTimeAction($this->target, $this->prototype, $this->callOption);
        $this->output .= $this->currentAction->executor->execute();
    }

    /**
     * Process all data or just a subset (chunk) of all data rows.
     * When multidimensional data is used this function is called recursive.
     * @param iterable|null $data The data set to be processed
     * Can be the whole set or just a subset (chunk) of the set. Not passing 
     * all data at once might reduce the amount of required memory. 
     * 
     * @param bool $finalize When true the end() method will be called after $data 
     *                       of the fist dimension ($dim = 0) has been processed.
     *                       When false this method might be called again with
     *                       other chunks of data.
     *                       To finalize the job $finalize need be true or end()
     *                       method must be called. 
     * @return string|object Result of end() as string when finalize is true or
     * $this when when finalize is false at first dimension to enable method chaining.
     */
    public function run(?iterable $data, bool $finalize = true) {
        if (isset($this->actions['init'])) {
            $this->finalInitializion();
        }
        $this->runPartial($data);
        if ($this->dim->id === 0) {
            return ($finalize) ? $this->end() : $this;
        }
    }

    /**
     * Iterate over a given data set.
     * Use to iterate over data sets from data dimension > 0.
     * Note: Make sure to call the run() method at least once and the end() method when 
     * you're ready. 
     * @param iterable|null $data
     */
    public function runPartial(?iterable $data): void {
        if (!empty($data)) {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        } elseIf ($this->dim->id > 0) {
            $this->noData_n();
        }
    }

    /**
     * Handles a single row.
     * @param array|object $row Data row to be processed.
     * @param string|int|null $rowKey Optional key of $row. Defaults to null.
     */
    public function next($row, $rowKey = null): self {
        $this->handleGroupChanges($row, $rowKey);
        // icrement row counter
        $this->rc->items[$this->dim->id]->inc();
        $this->dim->dataHandler->addValues($row, $rowKey, $this->total->items);
        // Handle next dimension or execute detail action.
        if (!$this->dim->isLastDim) {
            $this->handleDimension($row, $rowKey);
            // For better performance call detail action only when required.
        } elseif ($this->detailAction->execute) {
            $this->output .= $this->detailAction->executor->execute($row, $rowKey);
        }
        return $this;
    }

    /**
     * Detect group changes and execute related actions. 
     * 
     * Group change is true when values of group attributes in current row 
     * are not equal with group values of previous row in same dimension.
     * When group has changed header and footer actions will be executed.
     * @param array|object $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     */
    private function handleGroupChanges($row, $rowKey): void {
        $indexedValues = $this->dim->dataHandler->getGroupValues($row, $rowKey);
        // Check if group has changed. $diffs has an array with changed group values.
        // This is always true for next dimension (when groups are defined) 
        $diffs = array_diff_assoc($indexedValues, $this->dim->groupValues);
        if (empty($diffs)) {                 // group has not changed
            $this->changedLevel = null;
            $this->dim->row = $row;
            $this->dim->rowKey = $rowKey;
            return;
        }
        // Group has changed. Determine index of the highest changed group.
        $changedLevelInDim = key($diffs);
        $this->changedLevel = $changedLevelInDim + $this->dim->fromLevel;
        // No footer when row is the very first one (in dimension). 
        ($this->dim->row === null) ?: $this->handleFooters();
        $this->dim->activateValues($row, $rowKey, $indexedValues);
        // Call Header methods;
        $this->lowestHeader = $this->dim->lastLevel;
        $this->mp->level = $this->changedLevel;
        // Headers from changed group in dim to last group in dim
        foreach (array_slice($indexedValues, $changedLevelInDim) as $groupValue) {
            $this->gc->items[$this->mp->level]->inc();
            $group = $this->groups->items[$this->mp->level];
            $this->currentAction = $group->headerAction;
            $this->output .= $this->currentAction->executor->execute($groupValue, $this->dim->row, $this->dim->rowKey, $this->dim->id);
            $this->mp->level++;
        }
        $this->currentAction = $this->detailAction;
    }

    /**
     * Handle footers from lowest header level up to changed level.
     * After executing a footer all counter and totals must be cumulated to next level.
     */
    private function handleFooters(): void {
        for ($this->mp->level = $this->lowestHeader; $this->mp->level >= $this->changedLevel; $this->mp->level--) {
            $group = $this->groups->items[$this->mp->level];
            $this->dim = $this->dims[$group->dimID];
            $this->currentAction = $group->footerAction;
            $this->output .= $this->currentAction->executor->execute($this->dim->groupValues[$group->level - $this->dim->fromLevel],
                    $this->dim->row, $this->dim->rowKey, $this->dim->id);
            $this->rc->cumulateToNextLevel();
            $this->gc->cumulateToNextLevel();
            $this->total->cumulateToNextLevel();
        }
    }

    /**
     * Get data for next dimension out of current row.
     * When data equals false a called function will call run() or next() 
     * methods. When we get data from $row the run() method will be called.
     * With both ways run() or next () methods will be called recursive.
     * Dimension is incremented at each call and decremented at the end
     * of this method.
     * @param array|object $row The current data row. 
     * @param mixed $rowKey The key of $row
     */
    private function handleDimension($row, $rowKey): void {
        $changed = $this->noGroupChange();
        $this->currentAction = $this->dim->detailAction;
        $this->output .= $this->currentAction->executor->execute($this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->currentAction = $this->detailAction;
        $dataHandler = $this->dim->dataHandler;
        // Load next dimension
        $this->dim = next($this->dims);
        // Reset group values of new dim only when previos dim had a group change.
        (!$changed) ?: $this->dim->groupValues = [];
        $nextDimData = $dataHandler->getDimData($row, $rowKey);
        ($nextDimData === false) ?: $this->runPartial($nextDimData);
        $this->dim = prev($this->dims);
    }

    /**
     * Execute action when groups are defined but no group change occurred.
     * This situation occurs when current row has no distinct group values.
     * Rows might have unique 'key' values but group fields don't mirror them
     * (e.g. From a date field only year or month is declared as a group).
     * Only the owner of the data knows if this is expected or not. If it's
     * not expected action should raise a warning or exception. 
     * @return bool true when group has changed, false when not.
     */
    private function noGroupChange(): bool {
        if ($this->changedLevel !== null || empty($this->dim->dataHandler->numberOfGroups)) {
            return true;
        }
        $this->currentAction = $this->dim->noGroupChangeAction;
        $this->output .= $this->currentAction->executor->execute($this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->currentAction = $this->detailAction;
        return false;
    }

    /**
     * Finalize the job.
     * Execute either noData action or handle footers. Then totalFooter
     * and close actions will be executed.
     * @return string | null output. The collected return values of executed actions
     */
    public function end(): ?string {
        if ($this->rc->items[0]->sum(0) === 0) {
            $this->executeAction('noData');
        } elseif ($this->lowestHeader !== 0) {
            // only when groups are defined.
            $this->changedLevel = 1;
            $this->handleFooters();
        }
        $this->executeAction('totalFooter');
        $this->executeAction('close');
        return $this->output;
    }

    /**
     * Handle noData_n action.
     * Dim was set to next dimension. Use action of previous dim. 
     * Last dimension can't have an related action.
     */
    private function noData_n(): void {
        $this->dim = prev($this->dims);
        $this->currentAction = $this->dim->noDataAction;
        $this->output .= $this->currentAction->executor->execute($this->dim->id);
        $this->dim = next($this->dims);
    }

    /**
     * ******************************************************************************************
     * Following methods are extra sugar for target object. Not needed for program flow.        *
     * **************************************************************************************** */

    /**
     * Get the current group level or the level associated with the group name.
     * @param string $groupName The group name of the group. Null for the current group level.
     * @return int The requested group level.
     */
    public function getLevel(string $groupName = null): int {
        return ($groupName === null) ? $this->mp->level : $this->groups->groupLevel[$groupName];
    }

    /**
     * Get the level which triggered a group change
     * @return int|nulle The group level which triggered a group change. 
     * Null when no group change occurred.
     */
    public function getChangedLevel(): ?int {
        return $this->changedLevel;
    }

    /**
     * Get the dimID related to a group level.
     * @param string|int|null $level The group level for which the dimID will be returned. Defaults to the
     * current group level.
     * @return int The dimenion ID for the requested level. 
     */
    public function getDimID($level = null): int {
        if ($level === null) {
            return $this->dim->id;
        }
        $level = $this->mp->getLevel($level);
        return ($level === 0) ? 0 : $this->groups->items[$level]->dimID;
    }

    /**
     * Check if the current group action is executed the
     * first time within the given group level.
     * @param string|int|null $level The group level to be checked. Defaults to the
     * next higher group level.
     * @return bool true when the current action is executed the first time within
     * the given group level. False when not.
     */
    public function isFirst($level = null): bool {
        // For detail level compare with row counter of last group level.
        // Detail level can only be checked when in detail action. If $level is not null
        // it must then match the detail level. 
        if ($this->currentAction->actionKey === 'detail' && ($level === null || $level === $this->mp->level)) {
            return ($this->rc->items[$this->getDimID($level)]->sum($level - 1) === 1);
        }
        return ($this->gc->items[$this->getDimID($level)]->sum($this->getLevel($level) - 1) === 1);
    }

    /**
     * Check if the current groupFooter action is executed the
     * last time within the given group level.
     * In group headers or detail level this can't be answered (It would 
     * require to read the next row(s) ahead).
     * @param string|itn|null $level The group level to be checked. Defaults to the
     * next higher group level.
     * @return bool True when it is the last footer within $level, else false.
     * @throws \InvalidArgumentException when method is not called in a group footer
     * or asked for group levels not higher than the current one.
     */
    public function isLast($level = null): bool {
        if ($this->currentAction->actionKey !== 'groupFooter') {
            throw new \InvalidArgumentException('isLast() can only be answered in groupFooters');
        }
        $level = ($level === null) ? $this->mp->level - 1 : $this->mp->getLevel($level);
        if ($level >= $this->mp->level) {
            throw new \InvalidArgumentException('isLast() can check only for higher group levels');
        }
        return ($level > $this->changedLevel);
    }

    /**
     * Get active row for a given dimension.
     * @param int $dimID The dimension id. Defaults to null.
     * When $dimID is null rows of the current dimID will be returned.
     * If $dimID is negative the value will be subtracted from the current 
     * data dimension.
     * @return mixed The active data row for the requested dimension. 
     */
    public function getRow(int $dimID = null) {
        if ($dimID === null) {
            return $this->dim->row;
        }
        ($dimID >= 0) ?:             $dimID = $this->dim->id - $dimID;
        return $this->dims[$dimID]->row;
    }

    /**
     * Get the key of active row for the requested dimension. 
     * @param int $dimID The dimension id. Defaults to null.
     * When $dimID is null row key of the current dimID will be returned.
     * If $dimID is negative the value will be subtracted from the current 
     * data dimension.
     * @return mixed The key of the active row for the requested dimension.
     */
    public function getRowKey(int $dimID = null) {
        if ($dimID === null) {
            return $this->dim->rowKey;
        }
        ($dimID >= 0) ?:            $dimID = $this->dim->id - $dimID;
        return $this->dims[$dimID]->rowKey;
    }

    /**
     * Get all active group values. 
     * Note that in footer methods the row which triggered the group 
     * change is not yet active.
     * @return array Array having all values related to groups from first dimension to 
     * current dimension.
     */
    public function getGroupValues(): array {
        for ($i = 0; $i <= $this->dim->id; $i++) {
            // @todo merge values and assign group names 
        }

        return $this->groups->values;
    }

    /**
     * Get the active value for a given group.
     * @param null|int|string $group String representing the group name
     *        or integer representing the group level. When null it defaults to 
     *        the current group level. Negative values are substracted from the
     *        current level. 
     * @return mixed Current value of the requested group.
     */
    public function getGroupValue($group = null) {
        $groupLevel = $this->mp->getLevel($group);
        return $this->dim->groupValues[$groupLevel - $this->dim->fromLevel];
    }

    /**
     * Get all group names.
     * @return array The group names. Key is the associated level.
     */
    public function getGroupNames(): array {
        return array_flip($this->groups->groupLevel);
    }

    /**
     * Get the group name for a given group ID. 
     * @param int $groupLevel The level (ID) for which the group name will be returned.
     * Defaults to the current level.
     * @return string The group name of the requested level.
     */
    public function getGroupName(int $groupLevel = null): string {
        $groupLevel ??= $this->mp->level;
        return $this->groups->items[$groupLevel]->groupName;
    }

}
