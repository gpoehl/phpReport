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

use gpoehl\phpReport\Calculator\AbstractCalculator;
use gpoehl\phpReport\Calculator\CalculatorXS;
use gpoehl\phpReport\Getter\GetterFactory;
use gpoehl\phpReport\Output\AbstractOutput;
use gpoehl\phpReport\Collector;
use InvalidArgumentException;

/**
 * Main class of phpReport library.
 *
 * Handles group changes, computes values and joins multiple data sources.
 * Actions will be invoked on defined events.
 */
class Report {

    const VERSION = '3.5';

    protected string $configFilename = '';

    /** Object which collects output. */
    public AbstractOutput $out;

    /** @var Collection of row counters. One counter per dimension. */
    public Collector $rc;

    /** @var Collection of group counters. One group counter per group. */
    public Collector $gc;

    /** @var Collection of computed values, sheets and other collectors */
    public Collector $total;

    /** @var The currently executed action. */
    public Action $currentAction;

    /** @var Actions loaded from config file indexed by the ActionKey enum. */
    private \SplObjectStorage $actions;

    /** @var The runTimeActions for 'detail' events. */
    private readonly Action $detailHeaderAction;
    private readonly Action $detailAction;
    private readonly Action $detailFooterAction;

    /** @var Dimensions Object managing dimension objects. */
    public Dimensions $dims;

    /** @var The actual dimension object. Shortcut of current($dims). */
    private Dimension $dim;
//    public Dimension $dim;

    /** @var The current group level. */
    public int $currentLevel = 0;

    /** @var Highest level of changed group. Null when no group change detected. */
    private ?int $changedLevel;

    /** @var Groups object which holds any groups regardless of the related dimension. */
    private readonly Groups $groups;

    /** @var The option how and where event actions will be executed. */
    private RuntimeOption $runtimeOption = RuntimeOption::Default;

    /** @var Name of the prototype class */
    private string $prototypeName;

    /** @var The prototype object which executes prototype actions. Will be
      instantiated only when really needed. */
    public ?PrototypeBase $prototype = null;

    /** var Group level of lowest called header. This wil be the first footer.
     * The group level of the last dimension can't be used. When a dimension has
     * no data rows header for this (and following) dimension wasn't executed.
     */
    private int $lowestHeader = 0;

    /** @var True when the end() method is called. Only to make sure that
     * isLast() method works well for the last footers.
     */
    private bool $isJobDone = false;

    /** @var Action execution for levels above $skipLevel will be ignored. */
    private int|bool $skipLevel = false;

    /* @var Map objects implemented the 'cumulateToNextLevel' method to maxLevel as data */
    private \SplObjectStorage $cumulateMap;
    private string $detailName;

    /**
     * @param $target Default object or class name where action methods will be called.
     * @param $config Dynamic configuration to replace defaults set in config.php file.
     * @param $outputHandler The output handler to be used . Defaults to StringOutput handler. 
     * @param $params Optional parameters not used by this library itself.
     * Might be used as a data transfer vehicle.
     */
    final public function __construct(
            private object|string|null $target = null,
            null|array $config = null,
            AbstractOutput $outputHandler = null,
            public mixed $params = null,
    ) {

        $conf = new Configurator($config, $this->configFilename);
        $this->groups = new Groups($conf->totalName);
        $this->detailName = $conf->detailName;
        $this->actions = $conf->actions;
        $this->rc = new Collector();
        $this->gc = new Collector();
        $this->total = new Collector();
        $this->dims = new Dimensions();
        $this->dim = new Dimension($conf->dimensionName, $target);
        $this->dims->add($this->dim);
        $this->out = ($outputHandler) ? $outputHandler : new $conf->outputHandler();
        $this->prototypeName = $conf->prototype;
        $this->cumulateMap = new \SplObjectStorage();
        return $this;
    }

    /**
     * Join any data to the current row.
     * Every join creates a new data dimension.
     * @param string $name Unique dimension name.
     * @param mixed $source Source of the joined data {@see getter\BaseGetter::getValue()}
     * Callables must return an iterable data set or null when no data exists.
     * A callable must return False when the callable passes data to the nextSet()
     * or next() methods.
     * @param $actions Actions indexed by ActionKey or ActionKey->name to be executed instead of default actions.
     * @param mixed[] $params Optional parameters passed to callables getting joined data.
     */
    public function join(string $name, $source = null, iterable|null $actions = [], ... $params): self {
        $source ??= $name;
        GetterFactory::verifySource($source, $params);
        $this->dim->setJoinSource($source, $params);
        $validatedActions = $this->getValidatedActions($actions, 'dim');
        foreach (Actionkey::getKeysByGroup('dim') as $actionKey) {
            $action = (isset($validatedActions[$actionKey])) ? $validatedActions[$actionKey] : $this->actions[$actionKey];
            $this->dim->actions[$actionKey] = $this->getNewAction($actionKey, $action, $this->dim->id, $this->dim->name);
        }
        $this->dim = new Dimension($name, $this->target);
        $this->dims->add($this->dim);
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
     * @param $actions Actions indexed by ActionKey or ActionKey->name to be executed instead of default actions.
     * @param mixed[] $params Optional parameters passed to callables getting the group value.
     */
    public function group(string $name, $source = null, iterable|null $actions = [], ...$params): self {
        $source ??= $name;
        GetterFactory::verifySource($source, $params);

        $group = new Group($name, $this->dim->id, $source, $params);
        $this->currentLevel = $this->groups->addGroup($group);

        $validatedActions = $this->getValidatedActions($actions, 'group');
        foreach (Actionkey::getKeysByGroup('group') as $actionKey) {
            $action = (isset($validatedActions[$actionKey])) ? $validatedActions[$actionKey] : $this->actions[$actionKey];
            $group->actions[$actionKey] = $this->getNewAction($actionKey, $action, $group->level, $name);
        }
        $this->dim->addGroup($group);

        $calculator = new CalculatorXS;
        // Use method getLevel as first parameter
        $calculator->initialize($this->getLevel(...), $group->level);
        $this->gc->addItem($calculator, $group->level, $name);
        $this->cumulateMap[$calculator] = $group->level;
        return $this;
    }

    /**
     * Validate group or dimension actions.
     * Actions with type string will be replaced by the ActionKey enum object
     * @param $actions Actions from group() or join() methods
     * @param $actionGroup The ActionKey group to which the actions must belong
     * @return \WeakMap Prepared actions
     * @throws InvalidArgumentException
     */
    private function getValidatedActions($actions, $actionGroup): \WeakMap {
        $validatedActions = new \WeakMap();
        if ($actions !== null) {
            foreach ($actions as $actionKey => $action) {
                if (is_string($actionKey)) {
                    $actionKey = Actionkey::fromName($actionKey);
                }
                if ($actionKey->group() !== $actionGroup) {
                    throw new InvalidArgumentException("Invalid $actionGroup action.");
                }
                $validatedActions[$actionKey] = $action;
            }
        }
        return $validatedActions;
    }

    /**
     * Compute values.
     * Instantiates an calulator object to provide aggregate functions. The calculator
     * is linked to the total collector.
     * Aggregate functions are available at each group level at any time.
     * @param $name Unique name to reference a calculator object.
     * @param $source Source of the value to be computed.
     * When the $value parameter is null it defaults to the content of $name parameter.
     * Use False when the value should not be computed automaticly. In this case
     * only the referece to the calculator object will be established. Use the
     * calculators add() method to compute values.
     * @param $calculator Calculator object. Defaults to the CalculatorXS object.
     * @param $maxLevel The group level at which the value will be added.
     * Defaults to the maximum level of the current dimension. Might be less when
     * aggregated data are only needed on higher levels.
     * @param mixed[] $params Optional parameters passed to callables getting the value.
     */
    public function compute(
            string $name
            , $source = null
            , ?AbstractCalculator $calculator = new CalculatorXS
            , int|string|null $maxLevel = null
            , ...$params): self {
        $source ??= $name;
        $calculator ??= new CalculatorXS;
        $maxLevel = $this->initializeCalculator($calculator, $maxLevel);
        $this->cumulateMap[$calculator] = $maxLevel;
        $this->total->addItem($calculator, $name);
        if ($source !== false) {
            GetterFactory::verifySource($source, $params);
            $this->dim->setCalcSource($name, $source, $params);
        }
        return $this;
    }

    /**
     * Compute values in a sheet.
     * Sheet is a collection of calculators for a horizontal representation of a value.
     * @param $name Unique name to reference the sheet object. The sheet
     * is linked to the total collector.
     *
     * @param $keySource Source of the key value. If $keySource is an array
     * the key is treated as $keySource and the value as $source.
     * Use False when the value should not be computed automaticly. In this case
     * only the referece to the sheet object will be established. Use the
     * sheet add() method to compute values.
     *
     * @param $source Source of the value to be aggregated. Use Null when
     * the $keySource returns key and data in an array [key => value]
     *
     * @param $calculator Calculator object. Defaults to the CalculatorXS object.
     * @param mixed $fromKey To use a fixed sheet declare the first
     * calculator name. Pass an array when sheet names are not in an sequence.
     * Example ['young', 'mid-aged', 'old'l
     * Null for sheets where calculators are instantiated for each key value.
     * @param mixed $toKey The last calculator name for fixed sheet. FromKey
     * will be icremented until $toKey is reached.
     * @param $maxLevel The group level at which the value will be
     * added. Defaults to the maximum level of the dimension. Might be less when
     * aggregated data are only needed on higher levels.
     * @param mixed ...$params Optional list of parameters passed `unpacked`
     * to anonymous functions and callables getting the sheet key =>value pair.
     */
    public function sheet(
            string $name
            , $keySource
            , $source
            , ?AbstractCalculator $calculator = new CalculatorXS
            , int|string|null $maxLevel = null
            , ?array $keyParams = []
            , ...$params
    ): self {
        $calculator ??= new CalculatorXS;
        $maxLevel = $this->initializeCalculator($calculator, $maxLevel);
        $sheet = new Sheet($calculator);
        $this->cumulateMap[$sheet] = $maxLevel;
        $this->total->addItem($sheet, $name);
        if ($keySource !== false) {
            // Don't pass params to prevent raising warning. The might be use for keySource an source.
            GetterFactory::verifySource($keySource, $keyParams);
            if ($source !== null) {
                GetterFactory::verifySource($source, $params);
            }
            $this->dim->setSheetSource($name, $keySource, $source, $keyParams, $params);
        }
        return $this;
    }

    public function fixedSheet(
            string $name
            , $keySource
            , $source
            , int|string|iterable $fromKey
            , int|string|null $toKey = null
            , AbstractCalculator $calculator = new CalculatorXS
            , int|string|null $maxLevel = null
            , ?array $keyParams = []
            , ...$params
    ): self {
        $calculator ??= new CalculatorXS;
        $maxLevel = $this->initializeCalculator($calculator, $maxLevel);
        $sheet = new FixedSheet($calculator, $fromKey, $toKey);
        $this->cumulateMap[$sheet] = $maxLevel;
        $this->total->addItem($sheet, $name);
        if ($keySource !== false) {
            // Don't pass params to prevent raising warning. The might be use for keySource an source.
            GetterFactory::verifySource($keySource, $keyParams);
            if ($source !== null) {
                GetterFactory::verifySource($source, $params);
            }
            $this->dim->setSheetSource($name, $keySource, $source, $keyParams, $params);
        }
        return $this;
    }

    /**
     * Call the calculator initialize() method.
     * Verify that given maxlevel is not above current maxLevel
     * @param $maxLevel to be checked. Defaults to the current maxLevel.
     * @return The integer of maxLevel.
     * @throws InvalidArgumentException
     */
    private function initializeCalculator(AbstractCalculator $calculator, int|string|null $maxLevel): int {
        $maxLevel = $this->getLevel($maxLevel);

        if ($maxLevel > $this->currentLevel) {
            throw new InvalidArgumentException("MaxLevel $maxLevel must be equal or less maxLevel of dim({$this->maxLevel}).");
        }
        $calculator->initialize($this->getLevel(...), $maxLevel);
        return $maxLevel;
    }

    /**
     * Create and return a new action object.
     * @param $actionKey The actionKey enum.
     * @param $actionValue The action to be executed.
     * @param $id The group level or dimension ID to replace the %n string in $actionValue.
     * @param $name Name of group, total or dim to replace the %s and %S strings in $actionValue.
     * @return The new action object.
     */
    private function getNewAction(ActionKey $actionKey, $actionValue, int $id, string $name): Action {
        return new Action($actionKey, $this->out->actionKeyMapper[$actionKey], $actionValue, $name, $id);
    }

    /**
     * On first call of run() this method is called.
     * Set runTimeActions and execute Start and  TotalHeader actions.
     */
    private function finalInitializion(): void {
        foreach ($this->dims as $dim) {
            $calculator = new CalculatorXS;
            $calculator->initialize($this->getLevel(...), $dim->lastLevel);
            $this->rc->addItem($calculator);
            $this->cumulateMap[$calculator] = $dim->lastLevel;
        }
        $this->dim = $this->dims[0];
        $this->currentLevel = 0;
        $this->executeAction(Actionkey::Start);
        $this->executeAction(Actionkey::TotalHeader);

        $this->detailHeaderAction = $this->getNewAction(ActionKey::DetailHeader, $this->actions[ActionKey::DetailHeader], 0, $this->detailName);
        $this->detailAction = $this->getNewAction(ActionKey::Detail, $this->actions[ActionKey::Detail], 0, $this->detailName);
        $this->detailFooterAction = $this->getNewAction(ActionKey::DetailFooter, $this->actions[ActionKey::DetailFooter], 0, $this->detailName);

        unset($this->actions[Actionkey::Start], $this->actions[Actionkey::TotalHeader],
                $this->actions[Actionkey::GroupBefore], $this->actions[Actionkey::GroupFirst], $this->actions[Actionkey::GroupHeader],
                $this->actions[Actionkey::GroupFooter], $this->actions[Actionkey::GroupLast], $this->actions[Actionkey::GroupAfter],
                $this->actions[Actionkey::DimDetail], $this->actions[Actionkey::DimNoData], $this->actions[Actionkey::DimNoGroupChange],
                $this->actions[ActionKey::DetailHeader], $this->actions[ActionKey::Detail], $this->actions[ActionKey::DetailFooter],
        );
        $this->setRunTimeActions();
        $this->currentAction = $this->detailAction;
    }

    /**
     * Call magic method in prototype class. Will prepare some data related to 
     * the last executed action.
     * Prototype class will be instantiated on first call.
     * @return The default prototype class returns an html table with the prepared data.
     */
    public function prototype(): string {
        $this->prototype ??= new $this->prototypeName($this);
        return $this->prototype->magic();
    }

    /**
     * Set runttime option.
     * Call option is used by action ojects to detect if and how actions
     * are executed. Primary use is to activate prototyping.
     * Usally call option will be set at program start but can also set or
     * altered during program execution.
     * @param $runtimeOption @see enum RuntimeOption.
     */
    public function setRuntimeOption(RuntimeOption $runtimeOption): self {
        if ($runtimeOption->isPrototype() && !isset($this->prototype)) {
            $this->prototype = new $this->prototypeName($this);
        }
        $this->runtimeOption = $runtimeOption;
        // Rebuild runTimeActions only when finalInitialisation wasn't done already.
        if (!isset($this->actions[Actionkey::Start])) {
            $this->setRunTimeActions();
        }
        return $this;
    }

    /**
     * Set runtime actions for all actions which might be executed more than once.
     */
    private function setRunTimeActions(): void {
        $params = [$this->target, $this->prototype, $this->runtimeOption];
        // Group actions
        foreach ($this->groups->items as $group) {
            foreach ($group->actions as $actionKey => $action) {
                $action->setRunTimeTarget(...$params);
            }
        }
        // Dim actions. Actions for last dimension are set in main class.
        foreach ($this->dims as $dim) {
            if (!$dim->isLastDim) {
                foreach ($dim->actions as $actionKey => $action) {
                    $action->setRunTimeTarget(...$params);
                }
            } else {
                $this->detailHeaderAction->setRunTimeTarget(...$params);
                $this->detailAction->setRunTimeTarget(...$params);
                $this->detailFooterAction->setRunTimeTarget(...$params);
            }
        }
        $this->dims->rewind();
    }

    /**
     * Execute actions which don't have parameters and are executed only once.
     * @param string $key The action key of $this->actions
     */
    private function executeAction(Actionkey $key): void {
        $action = ($key->group() === 'total') ?
                $this->getNewAction($key, $this->actions[$key], 0, $this->groups->totalName) :
                new Action($key, $this->out->actionKeyMapper[$key], $this->actions[$key]);
        $action->setRuntimeTarget($this->target, $this->prototype, $this->runtimeOption);
        $this->execute($action);
    }

    /**
     * Execute all non detail actions.
     * Level will be always appendend to $params
     * @param Action $action The action to be executed.
     * @param mixed $params List of paramters to be passed to methods.
     * @return void or false False as return of before actions will prevent
     * normal action to be executed.
     */
    private function execute(Action $action, ... $params) {
        if (!$action->runtimeTarget) {
            return;
        }
        // Execute action
        if ($action->targetKey === Action::STRING) {
            $output = $action->target;
        } else {
            $this->currentAction = $action;
            $output = ($action->runtimeTarget)(... $params);
        }
        // No output handling 
        if ($output === null) {
            return;
        }
        // Before group action returned 'false'. End execution for this row.  
        if ($output === false && $action->key === ActionKey::GroupBefore) {
            return false;
        }
        if ($action->kind === Action::OUTPUT) {
            // Write output to Output object
            $this->out->write($output, $this->currentLevel, $action->outputKey);
        } else {
            // Action requested trigger message
            trigger_error($output, $action->kind);
        }
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
        if (empty($data)) {
            If ($this->dim->id > 0) {
                // Dimension 0 is handled in end(). Can't be here because data can
                // be feed by next() method.
                // Handling of data in other than default method must must handle noDataN
                // by themself.
                $this->noDataN();
            }
        } else {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        }
    }

    /**
     * Handles a single data row.
     * @param $row Data to be processed. Usually object or array, but also scalar values are accepted.
     * @param $rowKey Optional key of $row.
     */
    public function next($row, $rowKey = null): void {
        $this->handleGroupChanges($row, $rowKey);
        if ($this->skipLevel !== false) {
            return;
        }
        $this->rc->items[$this->dim->id]->inc();
        foreach ($this->dim->calcGetters as $name => $getter) {
            $this->total[$name]->add($getter->getValue($row, $rowKey));
        }
        // Handle next dimension or execute detail action.
        if (!$this->dim->isLastDim) {
            $this->handleDimension();
        } elseif ($this->detailAction->runtimeTarget) {
            $output = ($this->detailAction->targetKey === Action::STRING) ?
                    $this->detailAction->target :
                    ($this->detailAction->runtimeTarget)($row, $rowKey, $this->currentLevel);
            if ($output !== null) {
                // Action 'trigger_error()' for details makes no sense. Condition not checked.
                $this->out->write($output, $this->currentLevel, $this->detailAction->outputKey);
            }
        }
    }

    /**
     * Detect group changes and execute related actions.
     *
     * Group change is true when values of group attributes in current row
     * are not equal with group values of previous row in same dimension.
     * When group has changed header and footer actions will be executed.
     * @param $row The current row.
     * @param $rowKey The key of $row.
     */
    private function handleGroupChanges($row, $rowKey): void {
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
        $this->skipLevel = false;
        for ($i = $this->changedLevel; $i <= $this->dim->lastLevel; $i++) {
            $group = $this->groups->items[$i];
            $this->currentLevel = $group->level;
            $this->gc->items[$group->level]->inc();
            $params = [$groupValues[$group->level], $this->dim->row, $this->dim->rowKey, $this->currentLevel];
            if ($this->execute($group->actions[Actionkey::GroupBefore], ... $params) === false) {
                $this->skipLevel = $group->level;
                break;
            }
            $this->execute($group->actions[Actionkey::GroupHeader], ... $params);
        }
        if ($this->dim->isLastDim && $this->skipLevel === false) {
            $this->execute($this->detailHeaderAction, $row, $rowKey, $this->currentLevel);
            $this->currentAction = $this->detailAction;
        }
    }

    /**
     * Handle footers from lowest header level up to changed level.
     * After executing a footer all counter and totals must be cumulated to next level.
     */
    private function handleFooters(): void {
        for ($this->currentLevel = $this->lowestHeader; $this->currentLevel >= $this->changedLevel; $this->currentLevel--) {
            if ($this->skipLevel === false || $this->currentLevel >= $this->skipLevel) {
                $group = $this->groups->items[$this->currentLevel];
                $this->dim = $this->dims[$group->dimID];
                $params = [$this->dim->row, $this->dim->rowKey, $this->currentLevel];
                if ($this->dim->isLastDim && $this->currentLevel === $this->dim->lastLevel) {
                    $this->execute($this->detailFooterAction, ... $params);
                }
                $groupValue = $this->dim->groupValues[$this->currentLevel];
                $this->execute($group->actions[Actionkey::GroupFooter], $groupValue, ... $params);
                $this->execute($group->actions[Actionkey::GroupAfter], $groupValue, ... $params);
            }
            // Cumulation is required even for skipped levels.
            foreach ($this->cumulateMap as $value) {
                if ($this->currentLevel <= $this->cumulateMap->getInfo()) {
                    $this->cumulateMap->current()->cumulateToNextLevel($this->currentLevel);
                }
            }
            if ($this->out InstanceOf CumulateIF) {
                $this->out->cumulateToNextLevel($this->currentLevel);
            }
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
        $this->execute($this->dim->actions[Actionkey::DimDetail], $this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->currentAction = $this->detailAction;
        $prevDim = $this->dim;
        // Load next dimension
        $this->dim = $this->dims[$this->dim->id + 1];

        /**
          Clear group values of new dim only when previous dim had a group change.
          If one would cleard them even when previous dim had no group change the
          new dim wouldn't have any group values and so couldn't create any
          group header or footers.
         */
        (!$changed) ?: $this->dim->groupValues = [];

        $nextDimData = $prevDim->getJoinedData();
        // When data has been already handled $nextDimData must be false.
        // If not the default action 'nextSet' will be called. 
        ($nextDimData === false) ?: $this->nextSet($nextDimData);
        // Data of current dimension has been handled. Go back one level.
        $this->dim = $this->dims[$this->dim->id - 1];
    }

    /**
     * Invoke action when groups are defined for a dimension but no group change occurred.
     * This happens when current row has no distinct group values.
     * Either the declared groups don't match the real key(s) of the row
     * (e.g. From a date field only the year or month is declared as a group)
     * or your data aren't unique.
     * Usually the is not expected and by default an error will be thrown.
     * @return bool true when group has changed, false when not.
     */
    private function noGroupChange(): bool {
        if ($this->changedLevel !== null || empty($this->dim->groups)) {
            return true;
        }
        $this->execute($this->dim->actions[Actionkey::DimNoGroupChange], $this->dim->row, $this->dim->rowKey, $this->dim->id);
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
            $this->executeAction(ActionKey::NoData);
        } elseif ($this->lowestHeader !== 0) {
            // only when groups are defined.
            $this->changedLevel = 1;
            $this->isJobDone = true;
            $this->handleFooters();
        }
        $this->currentLevel = 0;
        $this->executeAction(ActionKey::TotalFooter);
        $this->executeAction(ActionKey::Finish);
        return $this->out->get();
    }

    /**
     * Handle action when row had no joined data.
     * Dim was set to next dimension. Use action and row of previous dim.
     */
    private function noDataN(): void {
        $this->dim = $this->dims[$this->dim->id - 1];
        $this->execute($this->dim->actions[Actionkey::DimNoData], $this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->dim = $this->dims[$this->dim->id + 1];
    }

    /**
     * ******************************************************************************************
     * Following methods are extra sugar for target object. Not needed for program flow.        *
     * **************************************************************************************** */

    /**
     * Get the current group level or the level associated with the group name.
     * @param $level Null will return the current group level.
     * Note that detail() is always the last group level.
     * A negative value will be subtracted from the current group level.
     * Any other numeric value will be returned as it is.
     * A string represents the group name and the related group level will be returned.
     * @return The requested group level.
     */
    public function getLevel(int|string|null $level = null): int {
        return match (true) {
            $level === null => $this->currentLevel,
            // Substract level when negative else return given level
            is_numeric($level) => $level >= 0 ? $level : $this->currentLevel + $level,
            // Should be group name
            isset($this->groups->groupLevel[$level]) => $this->groups->groupLevel[$level],
            default => trigger_error("Group '$level' does not exist.", E_USER_NOTICE),
        };
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
     * Get the dimID related to a group.
     * @param $level The group level for which the dimID will be returned.
     * @see getGroupLevel()
     * @return int The dimension ID for the requested level.
     */
    public function getDimId(int|string|null $level = null): int {
        if ($level === null) {
            return $this->dim->id;
        }
        $level = $this->getLevel($level);
        // Level of 0 relates always to the first dimension (having ID of 0)
        return ($level === 0) ? 0 : $this->groups->items[$level]->dimID;
    }

    /**
     * Test if the group at $level or the current row at detail level occurs the
     * first time in the group above.
     * @param $level The group level to be tested. Defaults to the current level.
     * $level must be equal or above then the current level.
     * @return True when it's the first row at detail level or the action at
     * the given group level is executed the first time within the above group level. 
     * False when not.
     * @throws InvalidArgumentException when given $level is below current level.
     */
    public function isFirst(string|int|null $level = null): bool {
        // On detail level compare with row counter in last group level.
        if ($this->currentAction->key === 'detail' && $level === null) {
            return ($this->rc->items[$this->dim->id]->sum() === 1);
        }

        $testLevel = $this->getLevel($level);
        if ($level !== null and $testLevel > $this->currentLevel) {
            throw new InvalidArgumentException('isFirst() makes no sense on lower group levels');
        }

        // Test against group counter. No need to use sum of higher level.
        return ($this->gc->items[$testLevel]->sum($testLevel) === 1);
    }

    /**
     * Test if the group at $level or the current row at detail level occurs the
     * last time in the group above or in any higher group. 
     * 
     * In group headers or detail level this can't be answered (It would
     * require to read the next row(s) ahead).
     * 
     * @param $level The group level to be tested. Defaults to the level above 
     * the current level. 
     * $level must be higher then the current level.
     * @return True when the current group  
     * or the last row at detail level -> not yet implemented
     * is the last one within the $level. 
     * False when not.
     * @throws InvalidArgumentException when given $level is below current level.
     */
    public function isLast(int|string|null $level = null): bool {
        if ($this->currentAction->key !== ActionKey::GroupFooter && $this->currentAction->key !== Actionkey::GroupLast && $this->currentAction->key !== Actionkey::GroupAfter) {
            throw new InvalidArgumentException('isLast() can only be answered in groupFooter or afterGroup methods. Called from ' . $this->currentAction->key);
        }

        $testLevel = $this->getLevel($level);
        if ($level !== null and $testLevel >= $this->currentLevel) {
            throw new InvalidArgumentException('isLast() can only test against higher group levels');
        }
        return ($this->isJobDone || $testLevel > $this->changedLevel);
    }

    /**
     * Get the current row for the requested dimension.
     * @param $dim The requested dimension. Defaults to null.
     * When $dim is null row of the current dimension will be returned.
     * If $dim is negative the value will be subtracted from the current
     * data dimensionID.
     * @return mixed The active row for the requested dimension.
     */
    public function getRow(string|int|null $dim = null) {
        return $this->dims[$this->getIntOfDimParam($dim)]->row;
    }

    /**
     * Get the key of current row for the requested dimension.
     * @param $dim The requested dimension. Defaults to null.
     * When $dim is null row key of the current dimension will be returned.
     * If $dim is negative the value will be subtracted from the current
     * data dimensionID.
     * @return mixed The key of the active row for the requested dimension.
     */
    public function getRowKey(string|int|null $dim = null) {
        return $this->dims[$this->getIntOfDimParam($dim)]->rowKey;
    }

    private function getIntOfDimParam(string|int|null $dim): int {
        return match (true) {
            $dim === null => $this->dim->id,
            is_string($dim) => $this->dims->names[$dim],
            $dim <= 0 => $this->dim->id + $dim,
            default => $dim,
        };
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
     * @return Array with requested group values indexed by group level.
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
     * @param $group Alias name of level. @see getLevel()
     * @return mixed Current value of the requested group.
     */
    public function getGroupValue(int|string|null $group = null) {
        $level = $this->getLevel($group);
        $dimID = $this->groups->items[$level]->dimID;
        return $this->dims[$dimID]->groupValues[$level];
    }

    /**
     * Get all group names.
     * @return Array of all group names indexed by the associated level.
     */
    public function getGroupNames(): array {
        return array_flip($this->groups->groupLevel);
    }

    /**
     * Get the group name for a given group level.
     * @param $groupLevel The group level for which the group name will be returned.
     * Defaults to the current level.
     * @return The group name of the requested level.
     */
    public function getGroupName(int $groupLevel = null): string {
        $groupLevel ??= $this->currentLevel;
        return $this->groups->items[$groupLevel]->name;
    }
}
