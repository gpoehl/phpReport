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
 * Main class of phpReport.
 * Accepts any data input and monitors values in defined group attributes
 * between two data rows. When given values are not equal actions for group
 * headers and group footers will be executed.
 * 
 * This class offers also differnt ways to calulate the (running) sum of any attribute.
 * Row counters and group counters are always active and can be used at any time.
 */
class Report {

    const VERSION = '1.0.0';

    // Collected return values from executed actions
    public $output;

    // Rules for executiong actions
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
    
 // @property MajorProperties $mp Holds major properties to be passed to cumulator objects
    public $mp;
     // @property Collector $rc Collector for row counters
    public $rc;
    // @property Collector $gc Collector for group counters
    public $gc;
    // @property Collector $total Collector of sum attributes and sheets to be cumulated
    public $total;
    public $userConfig;          // Optional user configuration. @see Configurator for details.   
    public $activeMethod;              // The current method (build by getCallable) 
      
      
    private $dims;                  // Array of dimension objects
    private $currentDimID = 0;      // ID of current dimension. Start at 0.
    private $dim;                   // The actual dimension. Shortcut of $dims[$currentDimID]; 
    private $maxDimID = 0;          // Total number of dimensions
    private $changedLevel;          // Highest level of changed group. Null when no change 
    private $groups;                // Array to hold group objects
   
    // @property Collector $collector The master collector. All items
    // within this collector will cumumlated to higher level on group change.
    private $collector;
   
    private $target;                   // Object which holds the methods to be called. Usally passed as $this
    private $callOption = self::CALL_EXISTING;      // One of the execution rules above
    // @property Prototype $prototyp Prototyp object to serve prototyp actions
    private $prototype = null;
    private $actions = [];             // Configurable mapping of action keys to actions.
    private $buildMethodsByGroupName;  // Indicates how group names should be build.
    private $lowestHeader = 0;         // Level of lowest header called to be used as start index for footers.
    private $detailMethod = [];        // Run time detail action   
    private $needFooter = false;       // Bool to avoid execution of footer actions on first row.

    /**
     * Load parameter from config file and save given parameters.
     * @param object $target Object which holds the methods to be called. Usually passed as $this.
     * @param array|null $config Dynamic configuration to replace defaults set in config.php file.
     */
    public function __construct($target, array $config = null) {
        $this->target = $target;
        $conf = Factory::configurator($config);
        $this->groups = new Groups($conf->grandTotalName);
        $this->buildMethodsByGroupName = $conf->buildMethodsByGroupName;
        $this->actions = $conf->actions;
        $this->userConfig = $conf->userConfig;
        $this->mp = Factory::properties();
        $this->collector = Factory::collector();
        $this->mp->rc = $this->rc = Factory::collector();
        $this->collector->addItem($this->rc, 'rc');
        $this->mp->gc = $this->gc = Factory::collector();
        $this->collector->addItem($this->gc, 'gc');
        $this->mp->total = $this->total = Factory::collector();
        $this->collector->addItem($this->total, 'total');
        $this->dims[] = $this->dim = new Dimension();
        return $this;
    }

    /**
     * Declare data for the next dimension.
     * Next dimension can be next dimension in an multi dimensional array or
     * data of an 1:n relationship where the related data is not part of the
     * current dimension. 
     * 
     * @param mixed $source Method, callable, closure or attribute name. 
     * Methods, callables and closures must return an iterable data set, null 
     * when no data exists. 
     * Methods, callables and closures might also pass data themselves to the
     * run() or next() methods. In this case they must return false.
     * To detect that $data is a method or callable ist must be an array. If that arrary
     * has only one parameter a method with the name in parameter will be 
     * called in the owner class.
     * Attribute name is the name of an attribute when current row is an object
     * or the array key when current row is an array.
     * 
     * @param mixed $noData Action to be executed when $data don't have any data.
     * Defaults to null to stay with noData_n action from configuration.
     * @param mixed $rowDetail Action to be executed for each data row of the current dimension.
     * Defaults to null to stay with data_n action from configuration.
     * @param mixed noGroupChangeParam Action to be executed when data row didn't
     * trigger a group change.
     * Defaults to null to stay with noGroupChangeParam action from configuration.
     * @param mixed $parameters Optional variadic list of additional parameters passed thrue 
     * to external methods. 
     */
    public function data($source, $noData = null, $rowDetail = null, $noGroupChange = null, $parameters = null): Report {
        if (is_array($source) && count($source) === 1) {
            // Make sure that method name is set to array index = 1
            $source = [null, end($source)];
        }
        if ($noData !== null) {
            $noData = Helper::buildMethodAction($noData, 'noData_n');
        }
        if ($rowDetail !== null) {
            $rowDetail = Helper::buildMethodAction($rowDetail, 'detail_n');
        }
        if ($noGroupChange !== null) {
            $noGroupChange = Helper::buildMethodAction($noGroupChange, 'noGroupChange_n');
        }
        $dim = end($this->dims);
        $dim->setParameter($source, $noData, $rowDetail, $noGroupChange, $parameters);
        $this->dims[] = new Dimension();
        $this->maxDimID ++;
        return $this;
    }

    /**
     * Declare attribute to be grouped.
     * This method must be called once for each attribute to be grouped.
     * Values of attributes will be compared to values of previous row. When 
     * they are not the same defined footer and header actions will be performed.   
     * @param string $name The group name. Can be the same as the attribute name.
     * This name will be used to build method names (depending on configuration
     * parameters). Must be unique between all dimensions.
     * @param mixed $value Attribute name when data row is an object or
     * the key when data row is array. It is also possiblbe to use a callable
     * (a closusre or an array having class and method parameters). 
     * @param mixed $headerAction Allows passing action for individual group header. 
     * False when default action should not be executed.
     * @param mixed $footerAction Allows passing action for individual group footer. 
     * False when default action should not be executed.
     * @return $this Allows chaining of method calls.
     */
    public function group($name, $value, $headerAction = null, $footerAction = null): Report {
        $dim = end($this->dims);
        $dim->groupAttr[$name] = $value;
        $group = $this->groups->newGroup($name, $this->maxDimID);
        if ($headerAction !== null) {
            $group->headerParam = Helper::buildMethodAction($headerAction, 'groupHeader');
        }
        if ($footerAction !== null) {
            $group->footerParam = Helper::buildMethodAction($footerAction, 'groupFooter');
        }
        $this->gc->addItem(Factory::cumulator($this->mp, $group->level - 1, self::XS), $group->level);
        return $this;
    }

    /**
     * Declare attribute to be summarized.
     * This method must be called once for each attribute to be summarized.
     * Values are summarized at each group level and can be returned at any time.
     * @param string $name The name to reference the cumulator object.
     * Cumulator objects are stored in $this->t->name.
     * Must be unique between all dimensions.
     * @param mixed $value The attribute name when data row is an object or
     * the key when data row is array. It is also possiblbe to use a callable
     * (a closusre or an array having class and method parameters). 
     * Use false when the value should not be added automaticly. Call cumulators
     * add() method yourself with desired value. 
     * @param int|null $typ The cumulator type. 
     * Typ is used to choose between three different cumulators. The first one
     * is called XS and has all the basic methods to cumulate the value. This is
     * the default cumulator to be used as this requires the minimum of ressoruces.  
     * The second one is called REGULAR and counts also not null and not zero
     * values.
     * The third one is called XL and handles on top of the second one also
     * min and max values. 
     * @param int|null $maxLevel The group level at which the value will be 
     * added. Defaults to the maximum level of the dimension. Might be less when
     * cumulated data are only needed on higher levels.
     * @return $this Allows chaining of method calls.
     */
    public function sum($name, $value, ?int $typ = self::XS, ?int $maxLevel = null): Report {
        $typ = ($typ) ?? self::XS;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $cum = Factory::cumulator($this->mp, $maxLevel, $typ);
        $this->total->addItem($cum, $name);
        if ($dataAttribute !== false) {
            $dim = end($this->dims);
            $dim->sumAttributes[$name] = $value;
        }
        return $this;
    }

    /**
     * Sheet is a collection of cumulators for a horizontal representation of a value.
     *  
     * @param type $name
     * @param type $source
     * @param type $keySource
     * @param int|null $typ
     * @param type $fromKey
     * @param type $toKey
     * @param type $maxLevel
     * @return $this
     */
    public function sheet($name, $value, $key, ?int $typ = self::XS, $fromKey = null, $toKey = null, $maxLevel = null): Report {
        $typ = ($typ) ?? self::XS;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $cum = Factory::sheet($this->mp, $maxLevel, $typ, $fromKey, $toKey);
        $this->total->addItem($cum, $name);
        if ($dataAttribute !== false) {
            $dim = end($this->dims);
            $dim->sheetAttributes[$name] = [$value, $key];
        }
        return $this;
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
     * On first call of run() this method is called.
     * Set fromLevel and lastLevel on dims, instantiate row counter and
     * eventually call init and totalHeader methods.
     */
    private function finalInitializion(): void {
        $totalGroupCount = 0;
        foreach ($this->dims as $dimID => $dim) {
            $noOfGroups = count($dim->groupAttr);
            if ($noOfGroups > 0) {
                $dim->fromLevel = $totalGroupCount + 1;
                $totalGroupCount += $noOfGroups;
            } else {
                $dim->fromLevel = $totalGroupCount;
            }
            $dim->lastLevel = $totalGroupCount;
            $this->rc->addItem(Factory::cumulator($this->mp, $dim->lastLevel, self::XS, $dimID));
        }
        $this->mp->gc->setMapper($this->groups->groupLevel);

        $this->mp->groupLevel = $this->groups->groupLevel;
        $this->mp->detailLevel = $dim->lastLevel + 1;
        $this->setRunTimeActions();
        $this->callMethod('init');
        $this->callMethod('totalHeader');
        // init and report headers are done. Array elements are no longer needed.
        unset($this->actions['init'], $this->actions['totalHeader']);
        $this->activeMethod = $this->detailMethod;
    }

    /**
     * Get prototype data for the last called owner method.
     * @return string A html formatted table with some information related to
     * the last called method.
     */
    public function prototype(): string {
        if ($this->prototype === null) {
            $this->prototype = new Prototype($this);
        }
        return $this->prototype->magic();
    }

    /**
     * Set call option.
     * Setting call option defines which action will be taken. Usally call option
     * will be set at program start but can also set or altered during program execution.
     * The call option influences which call action will be executed and if 
     * methods are called in the owner or the prototye object.
     * @param int $callOption One of the CALL_x constants.
     * @return Report $this to allow method chaining
     * @throws InvalidArgumentException
     */
    public function setCallOption(int $callOption): Report {
        if ($callOption < 0 || $callOption > 3) {
            throw new \InvalidArgumentException('Invalid call option');
        }
        if ($callOption >= self::CALL_PROTOTYPE && $this->prototype === null) {
            $this->prototype = new Prototype($this);
        }
        $this->callOption = $callOption;
        // only when run() was called
        if (!isset($this->actions['init'])) {
            $this->setRunTimeActions();
        }
        return $this;
    }

    /**
     * Set runtime actions for all actions which might be executed more than once.
     */
    private function setRunTimeActions(): void {
        $this->detailMethod = $this->getRuntimeAction('detail', $this->actions['detail']);
        foreach ($this->groups->items as $group) {
            $rep = $group->getGroupNameReplacement($this->buildMethodsByGroupName);
            $method = $group->headerParam ?? Helper::replacePercent($rep, $this->actions['groupHeader']);
            $group->headerAction = $this->getRuntimeAction('groupHeader', $method);
            $method = $group->footerParam ?? Helper::replacePercent($rep, $this->actions['groupFooter']);
            $group->footerAction = $this->getRuntimeAction('groupFooter', $method);
        }
        // Exclude last dimension. Has no data from data() method. 
        for ($dimID = 0, $ilen = count($this->dims) - 1; $dimID < $ilen; $dimID++) {
            $dim = $this->dims[$dimID];
            $method = ($dim->noDataParam) ?? Helper::replacePercent($dimID, $this->actions['noData_n']);
            $dim->noDataAction = $this->getRuntimeAction('noData_n', $method);
            $method = ($dim->rowDetail) ?? Helper::replacePercent($dimID, $this->actions['detail_n']);
            $dim->detailAction = $this->getRuntimeAction('detail_n', $method);
            $method = ($dim->noGroupChangeParam) ?? Helper::replacePercent($dimID, $this->actions['noGroupChange_n']);
            $dim->noGroupChangeAction = $this->getRuntimeAction('noGroupChange_n', $method);
        }
    }

    /**
     * Build runtime action.
     * Runtime action will be used to perform actions. 
     * Runtime action depends primarily on the call option and action type.
     * @param string $methodKey The key of config methods array. 
     * @param array $actionParameter First Element has the action type while 
     * the second element has the action to be performed. Action type has
     * been set in configuration class.
     * @return array|null When no action is required null will be returned. 
     * When an action has to be performed an array is build which has the 
     * following elements:
     * Element 0: The method key(given be $methodKey). 
     * Element 1: The action typ (One of the const action types).
     * Element 2: The action to be taken. When prototyp object should be called
     * this element is an array where first element is the prototyp object and 
     * the second element the prototyp method which equals the method key. To
     * allow the prototyp object to determine the normal action the action and typ
     * from actionparameter are stored in elemets 3 and 4.
     * Method calls in owner class are represented by an array where the first
     * element is the owner object and the second element the method name.
     * In other cases is is an callable array, a closure or a string. 
     * Element 3: For prototype class calls only. The action from $actionParameter
     * Element 4: For prototype class calls only. The action typ from $actionParameter
     */
    private function getRuntimeAction(string $methodKey, array $actionParameter): ?array {
        [$typ, $action] = $actionParameter;
        // Don't even call prototype action when action equals false
        if ($action === false) {
            return null;
        }
        // Warning and error typ will not be called in owner or prototye
        if ($typ >= self::WARNING) {
            return [$methodKey, $typ, $action];
        }
        // Call prototype regardless of the type. Prototype method is key
        if ($this->callOption === self::CALL_ALWAYS_PROTOTYPE) {
            return [$methodKey, self::CALLABLE, [$this->prototype, $methodKey], $action, $typ];
        }
        // String, closure or callable ([class, method] array)
        if ($typ !== self::METHOD) {
            return [$methodKey, $typ, $action];
        }
        // Normal method to be called in $target
        if ($this->callOption === self::CALL_ALWAYS || method_exists($this->target, $action)) {
            return [$methodKey, $typ, [$this->target, $action], $action, $typ];
        }
        // Call protoype
        if ($this->callOption === self::CALL_PROTOTYPE) {
            return [$methodKey, $typ, [$this->prototype, $methodKey], $action, $typ];
        }
        // no action is required
        return null;
    }

    /**
     * Call simple methods like init, totalheader and their conterparts
     * Those functions are called only once.
     * @param string $key The key of $this->actions
     */
    private function callMethod(string $key): void {
        $method = $this->getRuntimeAction($key, $this->actions[$key]);
        if ($method) {
            $this->activeMethod = $method;
            $this->output .= ($method[1] === self::STRING) ? $method[2] : $method[2]();
        }
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
        if (isset($data)) {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        } elseIf ($this->currentDimID > 0) {
            $this->noData_n();
        }
        if ($this->currentDimID === 0) {
            return ($finalize) ? $this->end() : $this;
        }
    }

    /**
     * Handles a single row.
     * Method is called from run() or directly on user request.
     * @param array|object $row Data row to be processed.
     * @param string|int|null $rowKey Optional key of $row. Defaults to null.
     * @return \gpoehl\phpReport\Report
     */
    public function next($row, $rowKey = null): Report {
        // When current dimension id equals the maximum dimension id the detail
        // action will be executed. If not the handleDimension() 
        $this->handleGroupChanges($row, $rowKey);
        $this->addValues($row, $rowKey);
        if ($this->currentDimID < $this->maxDimID) {
            $this->handleDimension($row, $rowKey);
        } elseif ($this->detailMethod) {
            // Detail action. Can't be a string action so don't check for it.
            $this->output .= ($this->detailMethod[2])($row, $rowKey);
        }
        return $this;
    }

    /**
     * Detect group changes and execute related actions. 
     * 
     * Detect if a group has changed within the current dimension.
     * Group change is true when values of group attributes in current row 
     * are not equal with values of group attributes in previous row of same dimension.
     * The level of highest changed group is stored at $this->changedLevel.
     * When group has changed header and footer actions will be executed.
     * 
     * @param array|object $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     */
    private function handleGroupChanges($row, $rowKey): void {
        $indexedValues = [];
//        if (is_object($row)) {
              if ($row instanceof \stdClass) {
            foreach ($this->dim->groupAttr as $attr) {
                $indexedValues[] = ($attr instanceof \Closure) ? $attr($row, $rowKey) : $row->$attr;
            }
        } else {
            foreach ($this->dim->groupAttr as $attr) {
                $indexedValues[] = ($attr instanceof \Closure) ? $attr($row, $rowKey) : $row[$attr];
            }
        }
        // Check if group has changed. $diffs has an array with changed group values.
        $diffs = array_diff_assoc($indexedValues, $this->dim->groupValues);
        if (empty($diffs)) {                 // group has not changed
            $this->dim->row = [$rowKey, $row];
            $this->changedLevel = 0;
            return;
        }
        // Group has changed. Calculate index of the highest changed group.
        $changedLevelInDim = key($diffs);
        $this->changedLevel = $changedLevelInDim + $this->dim->fromLevel;
        $this->dim->groupValues = $indexedValues;
        ($this->needFooter) ? $this->handleFooters($this->changedLevel) : $this->needFooter = true;
        $this->dim->row = [$rowKey, $row];
        // Group values from dim get active after handling footers. 
        $this->groups->setValues($this->dim->fromLevel, $indexedValues);
        // Call Header methods;
        $this->lowestHeader = $this->dim->lastLevel;
        $this->mp->level = $this->changedLevel;
        $groupValues = array_slice($indexedValues, $changedLevelInDim);
        foreach ($groupValues as $groupValue) {
            $this->gc->items[$this->mp->level]->inc();
            $this->ExcuteHeaderAndFooterActions($this->groups->items[$this->mp->level]->headerAction, $groupValue);
            $this->mp->level++;
        }
        $this->activeMethod = $this->detailMethod;
    }

    /**
     * Increment row counter and add values to sum() or sheet() attributes
     * @param array|object $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     */
    private function addValues($row, $rowKey): void {
        $this->rc->items[$this->currentDimID]->inc();
        if (is_object($row)) {
            foreach ($this->dim->sumAttributes as $name => $attr) {
                $this->total->items[$name]->add(($attr instanceof \Closure) ? $attr($row, $rowKey) : $row->$attr);
            }
            foreach ($this->dim->sheetAttributes as $name => $attr) {
                $key = ($attr[0] instanceof \Closure) ? $attr[0]($row, $rowKey) : $row->{$attr[0]};
                $this->total->items[$name]->add($key, ($attr[1] instanceof \Closure) ? $attr[1]($row, $rowKey) : $row->{$attr[1]});
            }
        } else {
            foreach ($this->dim->sumAttributes as $name => $attr) {
                $this->total->items[$name]->add(($attr instanceof \Closure) ? $attr($row, $rowKey) : $row[$attr]);
            }
            foreach ($this->dim->sheetAttributes as $name => $attr) {
                $key = ($attr[0] instanceof \Closure) ? $attr[0]($row, $rowKey) : $row[$attr[0]];
                $this->total->items[$name]->add($key, ($attr[1] instanceof \Closure) ? $attr[1]($row, $rowKey) : $row[$attr[1]]);
            }
        }
    }

    /**
     * Handle footers from lowest header level up to changed level.
     * @param int $changedLevel Highest level of changed group.
     */
    private function handleFooters(int $changedLevel): void {
        $groupValues = array_reverse(array_slice($this->groups->values, $changedLevel));
        $this->mp->level = $this->lowestHeader;
        foreach ($groupValues as $groupValue) {
            $this->ExcuteHeaderAndFooterActions($this->groups->items[$this->mp->level]->footerAction, $groupValue);
            $this->collector->cumulateToNextLevel();
            $this->mp->level--;
        }
    }

    /**
     * Execute a single header or footer action.
     * When action is a methode these arguments will be passed:
     * $groupValue  The current group value for the called level
     * $row         Row belonging to the current group (not to $this->currentDimID)
     * $rowkey      The key of the $row
     * $dimID       The ID of related dimension
     * @param array|null $action Group runtime action for header or footer to be performed.
     * @param mixed $groupValue The value belonging to the current group. 
     */
    private function ExcuteHeaderAndFooterActions(?array $action, $groupValue): void {
        if ($action) {
            if ($action[1] === self::STRING) {
                $this->output .= $action[2];
            } else {
                $this->activeMethod = $action;
                $dimID = $this->getCurrentDimID();
                $this->output .= $action[2](
                        $groupValue
                        , $this->getRow($dimID)
                        , $this->getRowKey($dimID)
                        , $dimID
                );
            }
        }
    }

    /**
     * Get data for next dimension based on current row and execute related action.
     * Method makes (direct or indirect) recursive calls to run() or next ()
     * methods.
     * Dimension is incremented at each call and decremented at the end
     * of this method.
     * @param array|object $row The current data row. 
     * @param mixed $rowKey The key of $row
     */
    private function handleDimension($row, $rowKey): void {
        $changed = $this->noGroupChange($row, $rowKey);
        $this->rowDetail($row, $rowKey);
        $dim = $this->dim;
        $this->currentDimID ++;
        $this->dim = $this->dims[$this->currentDimID];
        // Reset group values of current dim only when previous dim had a group change.
        if ($changed) {
            $this->dim->groupValues = [];
        }

        if ($dim->source instanceof \Closure) {
            // method is a callable
            $result = ($dim->source)($row, $rowKey, $this->currentDimID, ... $dim->parameters);
            if ($result !== false) {
                $this->run($result);
            }
        } elseif (is_array($dim->source)) {
            // A method must be called
            if ($dim->source[0] === null) {
                // Target class not given. Default for rows being an object is the
                // object itself, else it's the $target class
                $result = (is_object($row)) ?
                        $row->{$dim->source[1]}(... $dim->parameters) :
                        // $row is an array. $this->target will be called. Target has
                        $this->target->{$dim->source[1]}($row, $rowkey, $this->currentDimID, ... $dim->parameters);
            } else {
                $result = ($dim->source)($row, $rowkey, $this->currentDimID, ... $dim->parameters);
            }
            if ($result !== false) {
                $this->run($result);
            }
        } elseif (is_object($row)) {
            // Data parameter points to an attribute within row object
            $this->run($row->{$dim->source});
        } else {
            // Data parameter points to an attribute within an array
            (isset($row[$dim->source])) ? $this->run($row[$dim->source]) : $this->run(null);
        }

        $this->currentDimID --;
        $this->dim = $this->dims[$this->currentDimID];
    }

    /**
     * Execute action when groups are defined but no group change occurred
     * @param type $row
     * @param type $rowKey
     * @return bool true when group has changed, false when not.
     */
    private function noGroupChange($row, $rowKey): bool {
        if ($this->changedLevel !== 0 || empty($this->dim->groupAttr)) {
            return true;
        }
        $action = $this->dim->noGroupChangeAction;
        if ($action) {
            switch ($action[1]) {
                case self::WARNING:
                    trigger_error($action[2] . " RowKey = $rowKey", E_USER_NOTICE);
                    break;
                case self::ERROR:
                    throw new \RuntimeException($action[2] . " RowKey = $rowKey");
                default:
                    $this->activeMethod = $action;
                    $this->output .= ($action[1] === self::STRING) ? $action[2] : $action[2](
                                    $this->dim->row[1]
                                    , $this->dim->row[0]
                                    , $this->currentDimID);
                    $this->activeMethod = $this->detailMethod;
            }
        }
        return false;
    }

    /**
     * Execute data row detail action. 
     * @param type $row
     * @param type $rowKey
     */
    private function rowDetail($row, $rowKey): void {
        $action = $this->dim->detailAction;
        if ($action) {
            $this->activeMethod = $action;
            $this->output .= ($action[1] === self::STRING) ? $action[2] : $action[2](
                            $this->dim->row[1]
                            , $this->dim->row[0]
                            , $this->currentDimID);
            $this->activeMethod = $this->detailMethod;
        }
    }

    /**
     * Finalize the job.
     * Execute either noData action or handle footers. Then totalFooter
     * and close actions will be executed.
     * @return string output. The collected return values of executed actions
     */
    public function end() {
        if ($this->rc->items[0]->sum(0) === 0) {
            $this->callMethod('noData');
        } else {
            $this->handleFooters(1);
            $this->mp->level = 0;
        }
        $this->callMethod('totalFooter');
        $this->callMethod('close');
        return $this->output;
    }

    /**
     * Handle noData_n action for dimensions greater than 0
     */
    private function noData_n(): void {
        $dimID = $this->currentDimID - 1;
        $action = $this->dims[$dimID]->noDataAction;
        if ($action) {
            $this->activeMethod = $action;
            $this->output .= ($action[1] === self::STRING) ? $action[2] : $action[2]($dimID);
        }
    }

    /**
     * Get the current dimID. While in groupFooters it's the dim related to the
     * current group. 
     * @return int The dimId
     */
    public function getCurrentDimID(): int {
        if ($this->activeMethod[0] !== 'groupFooter') {
            return $this->currentDimID;
        }
        return $this->groups->items[$this->mp->level]->dimID;
    }

    /**
     * ******************************************************************************************
     * Following methods are extra sugar for target object. Not needed for program flow.        *
     * **************************************************************************************** */

    /**
     * Get geht current group level
     * @return int The currend group level.
     */
    public function getLevel(): int {
        return $this->mp->level;
    }

    /**
     * Is current action executed the first time within the next higer level.
     * @return bool true when the current action is executed the first time within
     * the next higer level or false when not.
     */
    public function isFirst(): bool {
        // For detail level rowCount() is checked while for other levels
        // the inGroupCount() is used.
        if ($this->activeMethod[0] !== 'detail') {
            return ($this->inGroupCount() === 1);
        }
        return ($this->rowCount() === 1);
    }

    /**
     * Return boolean wether the currently processed groupFooter is the
     * last time called within the next higher level.
     * In group headers or detail level this can't be answered. It would 
     * require to read the next row ahead.
     * @return bool True when it is the last, else false.
     */
    public function isLast(): bool {
        if ($this->activeMethod[0] !== 'groupFooter') {
            throw new \InvalidArgumentException('isLast() can only be answered in groupFooters');
        }
        return ($this->mp->level !== $this->changedLevel);
    }

    /**
     * Get last row read for a given dimension
     * @param int $dimID The dimension id. Defaults to null.
     * When $dimID is null rows of the current dimID will be returned.
     * If $dimID is negative row of the current dimID minus the givmen value
     * will be returned.
     * @return mixed The last row read in the given dimension 
     */
    public function getRow(int $dimID = null) {
        if ($dimID === null) {
            $dimID = $this->getCurrentDimID();
        } elseif ($dimID < 0) {
            $dimID = $this->getCurrentDimID() - $dimID;
        }
        return $this->dims[$dimID]->row[1];
    }

    /**
     * Get the key of last read row for a given dimension
     * @param int $dimID The dimension id. Defaults to null.
     * When $dimID is null or negative method getCurrentDimID() will be called.
     * If $dimID is negative this value will be subtracted from the result of 
     * the getCurrentDimID() call.
     * @return mixed The key of last read row on dimension $dimID 
     */
    public function getRowKey(int $dimID = null) {
        if ($dimID === null) {
            $dimID = $this->getCurrentDimID();
        } elseif ($dimID < 0) {
            $dimID = $this->getCurrentDimID() - $dimID;
        }
        return $this->dims[$dimID]->row[0];
    }

    /**
     * Get all active group values. 
     * In footer methods group the actual read values are not yet active:
     * All group values related to groups from first dimension to 
     * current dimension will be returned. 
     * @return array The actual group values.
     */
    public function getGroupValues(): array {
        return $this->groups->values;
    }

    /**
     * Get the active value for a given group.
     * @param null|int|string $groupID Get the group value for this group.
     * @return mixed The requested value.
     */
    public function getGroupValue($groupID = null) {
        return $this->groups->values[$this->mp->getLevel($groupID)];
    }

    /**
     * Get the associated group level of a group name.
     * When $groupName is not set the current level will be returned.
     * @param integer|string $groupName The name of the grouping field
     * @return integer The associated level from the groupName.
     */
    public function getGroupLevel(string $groupName): int {
        return $this->groups->groupLevel[$groupName];
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
     * @param int $groupID The ID (level) for which the group name will be returned.
     * Defaults to the current level.
     * @return string The group name of the requested level.
     */
    public function getGroupName(int $groupID = null): string {
        $key = $groupID ?? $this->mp->level;
        return $this->groups->items[$key]->groupName;
    }

}
