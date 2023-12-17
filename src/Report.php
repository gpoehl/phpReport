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

    const VERSION = '3.2';

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

    /** @var Action[] Actions loaded from config file indexed by the action key. */
    private array $actions = [];

    /** @var The runTimeAction for 'detail' event. */
    private readonly Action $detailHeaderAction;
    private readonly Action $detailAction;
    private readonly Action $detailFooterAction;

    /** @var Dimension[] Arrray of data dimension objects. */
    private array $dims = [];

    /** @var The actual dimension object. Shortcut of current($dims). */
    private Dimension $dim;

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
    private ?PrototypeInterface $prototype = null;

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

    /** @var Action execution for levels above $skipLevel will be ignored. */
    private int|bool $skipLevel = false;

    /* @var Map objects implemented the 'cumulateToNextLevel' method to maxLevel as data */
    private \SplObjectStorage $cumulateMap;

    /**
     * @param $target Default object or class name where action methods will be called.
     * @param $config Dynamic configuration to replace defaults set in config.php file.
     * @param $outputHandler The output handler to be used . Defaults to StringOutput handler. 
     * @param $params Optional parameters not used by this library itself.
     * Might be used as a data transfer vehicle.
     */
    public function __construct(private object|string|null $target = null,
            array $config = null,
            AbstractOutput $outputHandler = null,
            public mixed $params = null) {
        $conf = new Configurator($config);
        $this->groups = new Groups($conf->grandTotalName);
        $this->buildMethodsByGroupName = $conf->buildMethodsByGroupName;
        $this->actions = $conf->actions;
        $this->rc = new Collector();
        $this->gc = new Collector();
        $this->total = new Collector();
        $this->dims[] = $this->dim = new Dimension(0, 0, $target);
        $this->out = ($outputHandler) ? $outputHandler : new $conf->outputHandler();
        $this->prototypeName = $conf->prototype;
        $this->cumulateMap = new \SplObjectStorage();
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
        GetterFactory::verifySource($source, $params);
        $this->dim->setJoinSource($source, $params);
        $this->dim->noDataAction = $this->makeAction('noDataN', $noDataAction, $this->dim->id);
        $this->dim->noGroupChangeAction = $this->makeAction('noGroupChangeN', $noGroupChangeAction, $this->dim->id);
        $this->dim->detailAction = $this->makeAction('detailN', $dataAction, $this->dim->id);
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
    public function group($name, $source = null, $beforeAction = null, $headerAction = null, $footerAction = null, $afterAction = null, ...$params): self {
        $source ??= $name;
        GetterFactory::verifySource($source, $params);

        $group = new Group($name, $this->dim->id, $source, $params);
        $this->currentLevel = $this->groups->addGroup($group);

        $group->beforeAction = $this->makeAction('beforeGroup', $beforeAction, $group->level, $name);
        $group->headerAction = $this->makeAction('groupHeader', $headerAction, $group->level, $name);
        $group->footerAction = $this->makeAction('groupFooter', $footerAction, $group->level, $name);
        $group->afterAction = $this->makeAction('afterGroup', $afterAction, $group->level, $name);

        $this->dim->addGroup($group);

        $calculator = new CalculatorXS;
        // Use method getLevel as first parameter
        $calculator->initialize($this->getLevel(...), $group->level);
        $this->gc->addItem($calculator, $group->level, $name);
        $this->cumulateMap[$calculator] = $group->level;
        return $this;
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
     * Action objects are used to execute actions mapped to events.
     * @param $actionKey The event name.
     * @param array|null|false $actionParam
     * @param $level The group level or dimension ID.
     * @param $name Group or Totalx name which replaces the % sign in $actionParam
     * @return The new action object.
     */
    private function makeAction(string $actionKey, $actionParam, int $level, ?string $name = null): Action {
        if ($name === null) {
            // It's a dimension specific action
            $replacement = (string) $level;
            // Setting level for output classes
            $level = $this->dim->lastLevel;
        } else {
            $replacement = match ($this->buildMethodsByGroupName) {
                true => $name,
                false => (string) $level,
                'ucfirst' => ucfirst($name),
            };
        }
        $actionParam ??= $this->actions[$actionKey];
        return new Action($actionKey, $this->out->actionKeyMapper[$actionKey], $level, $actionParam, $replacement);
    }

    /**
     * On first call of run() this method is called.
     * Set runTimeActions and call init and totalHeader methods.
     */
    private function finalInitializion(): void {
        // instantiate row counter
        foreach ($this->dims as $dim) {
            $calculator = new CalculatorXS;
            $calculator->initialize($this->getLevel(...), $dim->lastLevel);
            $this->rc->addItem($calculator);
            $this->cumulateMap[$calculator] = $dim->lastLevel;
        }

        reset($this->dims);
        $this->dim = current($this->dims);
        $this->executeAction('init');
        $this->executeAction('totalHeader');
        $this->detailHeaderAction = new Action('detailHeader', $this->out->actionKeyMapper['detailHeader'], $this->currentLevel, $this->actions['detailHeader']);
        $this->detailAction = new Action('detail', $this->out->actionKeyMapper['detail'], $this->currentLevel, $this->actions['detail']);
        $this->detailFooterAction = new Action('detailFooter', $this->out->actionKeyMapper['detailFooter'], $this->currentLevel, $this->actions['detailFooter']);
        unset($this->actions['init'], $this->actions['totalHeader'], $this->actions['groupHeader'], $this->actions['groupFooter'],
                $this->actions['noDataN'], $this->actions['dataN'], $this->actions['noGroupChangeN'], $this->actions['detail']);
        $this->setRunTimeActions();
        $this->currentAction = $this->detailAction;
        $this->currentLevel = 0;
    }

    /**
     * Call magic method in prototype class. Will prepare some data related to 
     * the last executed action.
     * Prototype class will be instantiated on first call.
     * @return The default prototype class returns an html table with the prepared data.
     */
    public function prototype(): string {
        $this->prototype ??= new Prototype($this);
        return $this->prototype->magic();
    }

    /**
     * Set runttime option.
     * Call option is used by action ojects to detect if and how actions
     * are executed. Primary use is to activate prototyping.
     * Usally call option will be set at program start but can also set or
     * altered during program execution.
     * @param $runtimeOption @see enum RuntimeOption.
     * @param $prototype Optional prototype object instead of default prototype
     */
    public function setRuntimeOption(RuntimeOption $runtimeOption, PrototypeInterace $prototype = null): self {
        if ($prototype !== null) {
            $this->prototype = $prototype;
        } elseif ($runtimeOption->isPrototype() && !isset($this->prototype)) {
            $this->prototype = new $this->prototypeName($this);
        }
        $this->runtimeOption = $runtimeOption;
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
        $params = [$this->target, $this->prototype, $this->runtimeOption];

        // Group actions
        foreach ($this->groups->items as $group) {
            $group->beforeAction->setRunTimeTarget(...$params);
            $group->headerAction->setRunTimeTarget(...$params);
            $group->footerAction->setRunTimeTarget(...$params);
            $group->afterAction->setRunTimeTarget(...$params);
        }
        // Dim actions. Actions for last dimension are set in main class.
        foreach ($this->dims as $dim) {
            if (!$dim->isLastDim) {
                $dim->noDataAction->setRunTimeTarget(...$params);
                $dim->noGroupChangeAction->setRunTimeTarget(...$params);
                $dim->detailAction->setRunTimeTarget(...$params);
            } else {
                $this->detailHeaderAction->setRunTimeTarget(...$params);
                $this->detailAction->setRunTimeTarget(...$params);
                $this->detailFooterAction->setRunTimeTarget(...$params);
            }
        }
    }

    /**
     * Execute actions which don't have parameters and are executed only once.
     * @param string $key The action key of $this->actions
     */
    private function executeAction(string $key): void {
        $action = ($key === 'totalHeader' || $key === 'totalFooter') ?
                $this->makeAction($key, $this->actions[$key], 0, $this->groups->grandTotalName) :
                new Action($key, $this->out->actionKeyMapper[$key], 0, $this->actions[$key]);
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
            $params[] = $action->level;
            $output = ($action->runtimeTarget)(... $params);
        }
        // No output handling 
        if ($output === null) {
            return;
        }
        // End execution for this row.  
        if ($output === false && $action->key === 'beforeGroup') {
            return false;
        }
        if ($action->kind === Action::OUTPUT) {
            // Write output to Output object
            $this->out->write($output, $action->level, $action->outputKey);
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
        if (!empty($data)) {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        } elseIf ($this->dim->id > 0) {
            $this->noDataN();
        }
    }

    /**
     * Handles a single data row.
     * @param $row Data to be processed. Usually object or array, but also scalar values are accepted.
     * @param $rowKey Optional key of $row.
     */
    public function next($row, string|int|null $rowKey = null): void {
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
        $this->skipLevel = false;
        for ($i = $this->changedLevel; $i <= $this->dim->lastLevel; $i++) {
            $group = $this->groups->items[$i];
            $this->currentLevel = $group->level;
            $this->gc->items[$group->level]->inc();
            if ($this->execute($group->beforeAction, $groupValues[$group->level], $this->dim->row, $this->dim->rowKey) === false) {
                $this->skipLevel = $group->level;
                break;
            }
            $this->execute($group->headerAction, $groupValues[$group->level], $this->dim->row, $this->dim->rowKey);
        }
        if ($this->dim->isLastDim && $this->skipLevel === false) {
            $this->execute($this->detailHeaderAction, $row, $rowKey);
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
                if ($this->dim->isLastDim && $this->currentLevel === $this->dim->lastLevel) {
                    $this->execute($this->detailFooterAction, $this->dim->row, $this->dim->rowKey);
                }
                $this->execute($group->footerAction, $this->dim->groupValues[$this->currentLevel],
                        $this->dim->row, $this->dim->rowKey);
                $this->execute($group->afterAction, $this->dim->groupValues[$this->currentLevel],
                        $this->dim->row, $this->dim->rowKey);
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
        $this->execute($this->dim->detailAction, $this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->currentAction = $this->detailAction;
        $prevDim = $this->dim;
        // Load next dimension
        $this->dim = next($this->dims);
        // Reset group values of new dim only when previous dim had a group change.
        (!$changed) ?: $this->dim->groupValues = [];
        $nextDimData = $prevDim->getJoinedData();
        ($nextDimData === false) ?: $this->nextSet($nextDimData);
        $this->dim = prev($this->dims);
    }

    /**
     * Invoke action when groups are defined for a dimension but no group change occurred.
     * This happens when current row has no distinct group values.
     * Either the declared groups don't match the real key(s) of the row
     * (e.g. From a date field only the year or month is declared as a group)
     * or your data aren't unique.
     * Usually the is not expected and you might want to trigger an error.
     * @return bool true when group has changed, false when not.
     */
    private function noGroupChange(): bool {
        if ($this->changedLevel !== null || empty($this->dim->groups)) {
            return true;
        }
        $this->execute($this->dim->noGroupChangeAction, $this->dim->row, $this->dim->rowKey, $this->dim->id);
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
        $this->currentLevel = 0;
        $this->executeAction('totalFooter');
        $this->executeAction('close');
        return $this->out->get();
    }

    /**
     * Handle action when row had no joined data.
     * Dim was set to next dimension. Use action and row of previous dim.
     */
    private function noDataN(): void {
        $this->dim = prev($this->dims);
        $this->execute($this->dim->noDataAction, $this->dim->row, $this->dim->rowKey, $this->dim->id);
        $this->dim = next($this->dims);
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
     * Get the dimID related to a group level.
     * @param $level The group level for which the dimID will be returned.
     * @see getGroupLevel()
     * @return int The dimension ID for the requested level.
     */
    public function getDimID(int|string|null $level = null): int {
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
        if ($this->currentAction->key !== 'groupFooter' && $this->currentAction->key !== 'afterGroup') {
            throw new InvalidArgumentException('isLast() can only be answered in groupFooter or afterGroup methods. Called from ' . $this->currentAction->key);
        }

        $testLevel = $this->getLevel($level);
        if ($level !== null and $testLevel >= $this->currentLevel) {
            throw new InvalidArgumentException('isLast() can only test against higher group levels');
        }
        return ($this->isJobDone || $testLevel > $this->changedLevel);
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
        $dimID = ($dimID >= 0) ?: $dimID + $this->dim->id;
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
        $dimID = ($dimID >= 0) ?: $dimID + $this->dim->id;
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
