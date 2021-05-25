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
 * Handles group changes, computes values and joins multiple data sources.
 * When a named event occurs the mapped action will be executed. 
 */
class Report
{

    const VERSION = '3.0.0';
    // Rules to execute actions
    const CALL_EXISTING = 0;          // Call methods in owner class only when implemented. Default.
    const CALL_ALWAYS = 1;            // Call also not existing methods in owner class. Allows using magic function calls.
    const CALL_PROTOTYPE = 2;         // Call methods in prototype class when not implemented in owner class.
    const CALL_ALWAYS_PROTOTYPE = 3;  // Call methods in prototype class for any action.
    // Calculator class selection
    const XS = 1;                       // CalculatorXS class (default)
    const REGULAR = 2;                  // Calculator class (has not null and not zero counters)
    const XL = 3;                       // CalculatorXL class (has also min and max values) 

    /** Collected return values from executed actions. */
    public ?string $output = null;

    /** @var Major properties to be passed to calculator objects. */
    public MajorProperties $mp;

    /** @var Collection of row counters. One counter per dimension. */
    public Collector $rc;

    /** @var Collection of group counters. One group counter per group. */
    public Collector $gc;

    /** @var Collection of computed values, sheets and other collectors */
    public Collector $total;

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

    /** @var True when the end() method is called. Only to make sure that 
     * isLast() method works well for the last footers.
     */
    private bool $isJobDone = false;

    /**
     * @param $target Default object or class name where action methods will be called.
     * @param array|null $config Dynamic configuration to replace defaults set in config.php file.
     * @param $params Optional parameters not used by this library itself. 
     * Might be used as a data transfer vehicle.
     */
    public function __construct(private object|string|null $target = null,
            array $config = null,
            public mixed $params = null) {
        $conf = Factory::configurator($config);
        $this->groups = new Groups($conf->grandTotalName);
        $this->buildMethodsByGroupName = $conf->buildMethodsByGroupName;
        $this->actions = $conf->actions;
        $this->mp = Factory::properties();
        $this->mp->rc = $this->rc = Factory::collector();
        $this->mp->gc = $this->gc = Factory::collector();
        $this->mp->total = $this->total = Factory::collector();
        $this->dims[] = $this->dim = new Dimension(0, 0, $target);
        return $this;
    }

    /**
     * Join any data to the current row.
     * Every join creates a new data dimension. 
     * @param mixed $source Source of the joined data {@see getter\BaseGetter::getValue()}
     * Callables must return an iterable data set or null when no data exists. 
     * A callable must return False when the callable passes data to the nextSet()
     * or next() methods.
     * @param mixed $noDataAction Action to be executed when no joined data are found.
     * Null for default action.
     * @param mixed $dataAction Action to be executed for each data row of the current dimension.
     * Null for default action.
     * @param mixed $noGroupChangeAction Action to be executed when current data row didn't
     * trigger a group change.
     * Null for default action.
     * @param mixed[] $params Optional parameters passed to callables getting joined data.
     */
    public function join($source, $noDataAction = null, $dataAction = null, $noGroupChangeAction = null, ... $params): self {
        getter\GetterFactory::verifySource($source, $params);
        $this->dim->setJoinSource($source, $params);
        $this->dim->noDataAction = $this->makeAction('noData_n', $noDataAction, $this->dim->id);
        $this->dim->noGroupChangeAction = $this->makeAction('noGroupChange_n', $noGroupChangeAction, $this->dim->id);
        $this->dim->detailAction = $this->makeAction('detail_n', $dataAction, $this->dim->id);
        $this->dims[] = $this->dim = new Dimension($this->dim->id + 1, $this->dim->lastLevel, $this->target);
        return $this;
    }

    /**
     * Declare a new data group.
     * When values of two consecutive data rows aren't equal footer and header events 
     * are triggered and mapped actions executed. Data related to a group can be accessed
     * by group name or the group level. 
     * A group counter (calculator object) will be instantiated and assigned to
     * the **gc** collector. 
     * @param string $name Unique group name. 
     * This name will be used to build method names ({@see Configuration})
     * @param mixed $source Source of the group value {@see getter\BaseGetter::getValue()}.
     * Defaults to the group name.
     * @param mixed $headerAction Action for group header. Null for default action.
     * @param mixed $footerAction Action for group footer. Null for default action.
     * @param mixed[] $params Optional parameters passed to callables getting the group value.
     */
    public function group($name, $source = null, $headerAction = null, $footerAction = null, ...$params): self {
        $source ??= $name;
        getter\GetterFactory::verifySource($source, $params);
        $group = new Group($name, ++$this->mp->maxLevel, $this->dim->id, $source, $params);
        If ($this->buildMethodsByGroupName === 'ucfirst') {
            $replacement = ucfirst($name);
        } else {
            $replacement = ($this->buildMethodsByGroupName) ? $name : (string) $group->level;
        }
        $group->headerAction = $this->makeAction('groupHeader', $headerAction, $replacement);
        $group->footerAction = $this->makeAction('groupFooter', $footerAction, $replacement);
        $this->groups->addGroup($group);
        $this->dim->groups[] = $group;
        $this->dim->lastLevel = $group->level;
        $this->gc->addItem(Factory::calculator($this->mp, $group->level - 1, self::XS), $group->level);
        $this->gc->setAltKey($name, $group->level);
        return $this;
    }

    /**
     * Compute values.
     * Instantiates an calulator object to provide aggregate functions. The calculator
     * is linked to the total collector.
     * Aggregate functions are available at each group level at any time.
     * @param string $name Unique name to reference a calculator object. 
     * @param mixed $source Source of the value to be computed.
     * When the $value parameter is null it defaults to the content of $name parameter.  
     * Use False when the value should not be computed automaticly. In this case
     * only the referece to the calculator object will be established. Use the
     * calculators add() method to compute values.  
     * @param int|null $typ The calculator type. 
     * Typ is used to choose between a calculator class. Options are XS, REGULAR
     * and XL. Defaults to XS.
     * @param int|null $maxLevel The group level at which the value will be 
     * added. Defaults to the maximum level of the current dimension. Might be less when
     * aggregated data are only needed on higher levels.
     * @param mixed[] $params Optional parameters passed to callables getting the value.
     */
    public function compute(string $name, $source = null, ?int $typ = self::XS, ?int $maxLevel = null, ...$params): self {
        $typ ??= self::XS;
        $source ??= $name;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $this->total->addItem(Factory::calculator($this->mp, $maxLevel, $typ), $name);
        if ($source !== false) {
            getter\GetterFactory::verifySource($source, $params);
            $this->dim->setCalcSource($name, $source, $params);
        }
        return $this;
    }

    /**
     * Compute values in a sheet.
     * Sheet is a collection of calculators for a horizontal representation of a value.
     * Call this method once for each sheet. 
     * @param string $name Unique name to reference the sheet object. The
     * reference will be hold in $this->total.
     * 
     * @param mixed $keySource Source of the key value. The source can return an array
     * having data to be computed indexed by key.
     * Use False when the value should not be computed automaticly. In this case
     * only the referece to the sheet object will be established. Use the
     * sheet add() method to compute values.  
     *   
     * @param mixed $source Source of the value to be aggregated. Use Null when
     * the $keySource returns key and data in an array [key => value]
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
    public function sheet(string $name, $keySource, $source, ?int $typ = self::XS, 
            $fromKey = null,
            $toKey = null, 
            ?int $maxLevel = null, 
            ?array $keyParams = [],
            ...$params
            ): self {
        $typ ??= self::XS;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $this->total->addItem(Factory::sheet($this->mp, $maxLevel, $typ, $fromKey, $toKey), $name);
        if ($keySource !== false) {
            // Don't pass params to prevent raising warning. The might be use for keySource an source. 
            getter\GetterFactory::verifySource($keySource, $keyParams);
            if ($source !== null) {
                getter\GetterFactory::verifySource($source, $params);
            }
            $this->dim->setSheetSource($name, $keySource, $source, $keyParams, $params);
        }
        return $this;
    }

    /**
     * Verify that maxlevel of sheet is not above current maxLevel
     * @param int $maxLevel to be checked. Defaults to the current maxLevel.
     * @return int maxLevel
     * @throws InvalidArgumentException
     */
    private function checkMaxLevel(?int $maxLevel): int {
        if ($maxLevel === null) {
            return $this->mp->maxLevel;
        }
        if ($maxLevel > $this->mp->maxLevel) {
            throw new InvalidArgumentException("MaxLevel $maxLevel must be equal or less maxLevel of dim({$this->mp->maxLevel}).");
        }
        return $maxLevel;
    }

    /**
     * Create and return a new action object.
     * Action objects are used to execute actions mapped to events.
     * @param string $actionKey The event name.
     * @param array|null|false $actionParam
     * @param mixed $replacement
     * @return Action The new action object.
     */
    private function makeAction(string $actionKey, $actionParam, $replacement): Action {
        $wrk = ($actionParam !== null) ? Helper::buildMethodAction($actionParam, $actionKey) :
                Helper::replacePercent((string) $replacement, $this->actions[$actionKey]);
        return new Action($actionKey, $wrk);
    }

    /**
     * On first call of run() this method is called.
     * Set runTimeActions and call init and totalHeader methods.
     */
    private function finalInitializion(): void {
        foreach ($this->dims as $dim) {
            $this->rc->addItem(Factory::calculator($this->mp, $dim->lastLevel, self::XS));
        }
        reset($this->dims);
        $this->dim = current($this->dims);
        $this->mp->groupLevel = $this->groups->groupLevel;
        $this->executeAction('init');
        $this->executeAction('totalHeader');
        $this->detailAction = new Action('detail', $this->actions['detail']);
        unset($this->actions['init'], $this->actions['totalHeader'], $this->actions['groupHeader'], $this->actions['groupFooter'],
                $this->actions['noData_n'], $this->actions['data_n'], $this->actions['noGroupChange_n'], $this->actions['detail']);
        $this->setRunTimeActions();
        $this->currentAction = $this->detailAction;
    }

    /**
     * Call prototype class to prepare some data related to the last executed action.
     * @return string A html table with the prepared data.
     */
    public function prototype(): string {
        $this->prototype ??= new Prototype($this);
        return $this->prototype->magic();
    }

    /**
     * Set call option.
     * Call option is used by action ojects to detect if and how actions
     * are executed. Primary use is to activate prototyping. 
     * Usally call option will be set at program start but can also set or 
     * altered during program execution.
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
        $params = [$this->target, $this->prototype, $this->callOption];
        $this->detailAction->setRunTimeAction(...$params);
        foreach ($this->groups->items as $group) {
            $group->headerAction->setRunTimeAction(...$params);
            $group->footerAction->setRunTimeAction(...$params);
        }
        // Exclude last dimension. Has no data from data() method. 
        foreach ($this->dims as $dim) {
            if (!$dim->isLastDim) {
                $dim->noDataAction->setRunTimeAction(...$params);
                $dim->noGroupChangeAction->setRunTimeAction(...$params);
                $dim->detailAction->setRunTimeAction(...$params);
            }
        }
    }

    /**
     * Execute actions which don't have parameters and are executed only once.
     * @param string $key The action key of $this->actions
     */
    private function executeAction(string $key): void {
        $this->currentAction = new Action($key, $this->actions[$key]);
        $this->currentAction->setRunTimeAction($this->target, $this->prototype, $this->callOption);
        $this->output .= $this->currentAction->execute();
    }

    /**
     * Start the real program execution after calling configuration methods.
     * 
     * Either pass all data or set $complete to false.
     * In the latter case call nextSet() or next() methods to pass further data
     * and call the end() method when you're done. 
     * @param iterable|null $data Null or iterable data set.
     * @param bool $complete True to finish the job after handling $data.
     * False to allow passing more data. 
     * @return string|self When $complete is true the collected output will be
     * returned. Else the current object will returned to enable method chaining.
     */
    public function run(?iterable $data, bool $complete = true) {
        $this->finalInitializion();
        $this->nextSet($data);
        return ($complete) ? $this->end() : $this;
    }

    /**
     * Iterate over a given data set.
     * Data set can be the whole data set or just a partial set (chunk or batch).
     * @param iterable|null $data
     */
    public function nextSet(?iterable $data): void {
        if (!empty($data)) {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        } elseIf ($this->dim->id > 0) {
            $this->noData_n();
        }
    }

    /**
     * Handles a single data row.
     * @param array|object $row Data row to be processed.
     * @param $rowKey Optional key of $row. Defaults to null.
     */
    public function next($row, string|int|null $rowKey = null): void {
        $this->handleGroupChanges($row, $rowKey);
        $this->rc->items[$this->dim->id]->inc();
        foreach ($this->dim->calcGetters as $name => $getter) {
            $this->total[$name]->add($getter->getValue($row, $rowKey));
        }
        // Handle next dimension or execute detail action.
        if (!$this->dim->isLastDim) {
            $this->handleDimension();
        } else {
            $this->output .= $this->detailAction->execute($row, $rowKey, $this->dim->id);
        }
    }

    /**
     * Detect group changes and execute related actions. 
     * 
     * Group change is true when values of group attributes in current row 
     * are not equal with group values of previous row in same dimension.
     * When group has changed header and footer actions will be executed.
     * @param array|object $row The current row.
     * @param $rowKey The key of $row. 
     */
    private function handleGroupChanges($row, string|int|null $rowKey): void {
        $groupValues = $this->dim->getGroupValues($row, $rowKey);
        // Check if group has changed. $diffs has an array with changed group values.
        // This is always true for next dimension (when groups are defined) 
        $diffs = array_diff_assoc($groupValues, $this->dim->groupValues);
        if (empty($diffs)) {                 // group has not changed
            $this->changedLevel = null;
            $this->dim->row = $row;
            $this->dim->rowKey = $rowKey;
            return;
        }
        // Group has changed. Determine index of the highest changed group.
        $this->changedLevel = key($diffs);
        // No footer for first row (in actual dimension). 
        ($this->dim->row === null) ?: $this->handleFooters();
        $this->dim->activateValues($row, $rowKey, $groupValues);
        // Call Header methods from changed group in dim to last group in dim;
        // Array slice with negative offset!
        $this->lowestHeader = $this->dim->lastLevel;
        foreach (array_slice($this->dim->groups, $this->changedLevel - $this->dim->lastLevel - 1) as $group) {
            $this->mp->level = $group->level;
            $this->gc->items[$group->level]->inc();
            $this->currentAction = $group->headerAction;
            $this->output .= $this->currentAction->execute($groupValues[$group->level], $this->dim->row, $this->dim->rowKey, $this->dim->id);
        }
        $this->currentAction = $this->detailAction;
    }

    /**
     * Handle footers from lowest header level up to changed level.
     * After executing a footer all counter and totals must be cumulated to next level.
     */
    private function handleFooters(): void {
        for ($this->mp->level = $this->lowestHeader; $this->mp->level >= $this->changedLevel; $this->mp->level--) {
            $wrk = null;
            $group = $this->groups->items[$this->mp->level];
            $this->dim = $this->dims[$group->dimID];
            $this->currentAction = $group->footerAction;
            $this->output .= $this->currentAction->execute($this->dim->groupValues[$this->mp->level],
                    $this->dim->row, $this->dim->rowKey, $this->dim->id);
            $this->rc->cumulateToNextLevel();
            $this->gc->cumulateToNextLevel();
            $this->total->cumulateToNextLevel();
        }
    }

    /**
     * Get data for next dimension.
     * When data equals false a called function will call run() or next() 
     * methods. When we get data from $row the run() method will be called.
     * With both ways run() or next () methods will be called recursive.
     * Dimension is incremented at each call and decremented at the end
     * of this method.
     */
    private function handleDimension(): void {
        $changed = $this->noGroupChange();
        $this->currentAction = $this->dim->detailAction;
        $this->output .= $this->currentAction->execute($this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->currentAction = $this->detailAction;
        $prevDim = $this->dim;
        // Load next dimension
        $this->dim = next($this->dims);
        // Reset group values of new dim only when previos dim had a group change.
        (!$changed) ?: $this->dim->groupValues = [];
        $nextDimData = $prevDim->getJoinedData();
        ($nextDimData === false) ?: $this->nextSet($nextDimData);
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
        if ($this->changedLevel !== null || empty($this->dim->groups)) {
            return true;
        }
        $this->currentAction = $this->dim->noGroupChangeAction;
        $this->output .= $this->currentAction->execute($this->dim->row, $this->dim->rowKey, $this->dim->id);
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
            $this->isJobDone = true;
            $this->handleFooters();
        }
        $this->executeAction('totalFooter');
        $this->executeAction('close');
        return $this->output;
    }

    /**
     * Handle action when row had no joined data.
     * Dim was set to next dimension. Use action and row of previous dim. 
     */
    private function noData_n(): void {
        $this->dim = prev($this->dims);
        $this->currentAction = $this->dim->noDataAction;
        $this->output .= $this->currentAction->execute($this->dim->row, $this->dim->rowKey, $this->dim->id);
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
        // it must match the detail level. 
        if ($this->currentAction->actionKey === 'detail' && ($level === null || $level === $this->mp->level)) {
            return ($this->rc->items[$this->getDimID($level)]->sum($level) === 1);
        }
        return ($this->gc->items[$this->getDimID($level)]->sum($this->getLevel($level) - 1) === 1);
    }

    /**
     * Check if the footer of the current group is last one within the given group.
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
        return ($this->isJobDone || $level >= $this->changedLevel);
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
        ($dimID >= 0) ?: $dimID = $this->dim->id - $dimID;
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
        ($dimID >= 0) ?: $dimID = $this->dim->id - $dimID;
        return $this->dims[$dimID]->rowKey;
    }

    /**
     * Get active group values. 
     * Note that in footer methods the row which triggered the group 
     * change is not yet active.
     * @param int $dimID The dimension id for / till the group values will be returned.
     * Defaults to the current dimension id.
     * @param bool $fromFirstLevel When true all group values from the first 
     * dimension to the requested dimension are returned. When false only the
     * group values of the requested dimension are returned.
     * @return array Array with requested group values indexed by group level.
     */
    public function getGroupValues(?int $dimID = null, bool $fromFirstLevel = true): array {
        $dimID ??= $this->dim->id;
        if (!$fromFirstLevel) {
            return $this->dims[$dimID]->groupValues;
        }
        $wrk = $this->dims[0]->groupValues;
        for ($i = 1; $i <= $this->dimId; $i++) {
            $wrk += $this->dims[0]->groupValues;
        }

        return $wrk;
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
        $dimID = $this->groups->items[$groupLevel]->dimID;
        return $this->dims[$dimID]->groupValues[$groupLevel];
    }

    /**
     * Get all group names.
     * @return array The group names. Key is the associated level.
     */
    public function getGroupNames(): array {
        return array_flip($this->groups->groupLevel);
    }

    /**
     * Get the group name for a given group level. 
     * @param int $groupLevel The group level for which the group name will be returned.
     * Defaults to the current level.
     * @return string The group name of the requested level.
     */
    public function getGroupName(int $groupLevel = null): string {
        $groupLevel ??= $this->mp->level;
        return $this->groups->items[$groupLevel]->name;
    }

}
