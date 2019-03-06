<?php

declare(strict_types=1);
/**
 * @copyright Copyright &copy; bbr.de, 2016 - 2017
 * @version 1.0.0
 * @author Günter Pöhl <guenter-poehl@hausverwaltung-poehl.de>
 * @link http://www.bbr.com/
 * @license http://www.bbr.de/license/
 */

namespace gpoehl\phpReport;

/**
 * Backbone for all tasks to handle group changes and calculate (sub)totals. 
 * This class takes care of all staff required to manage group changes in a set of data.
 * Grouping values can be accessed by their original keys as well as by the associated
 * level. Levels start with 0 for grand totals and increments for each user defined
 * group. All internal methods (also the Calculator) use the level as key.
 *  
 */
class Report {
    const VERSION = '1.0.0';

    public $output;     // Collected data returned from called methods 

    // Set rules when methods will be called in prototype class.
    // Rules don't apply to 'noData' and 'fetchValues' methods.
    // Rules also don't apply when a closure, a class, method array or a string 
    // should be used instead of default methods. 
    // Prototype options
    const CALL_EXISTING = 0;          // Call methods in owner class only when implemented. Default.
    const CALL_ALWAYS = 1;            // Call always methods in owner class. Allows using magic function calls.
    const CALL_PROTOTYPE = 2;         // Call methods in prototype class when not implemented in owner class.
    const CALL_ALWAYS_PROTOTYPE = 3;  // Call methods always in prototype class.
    // Action types
    const STRING = 0;
    const CLOSURE = 1;
    const CALLABLE = 2;
    const METHOD = 3;

    private $dims;               // Array of dimension objects
    public $currentDimID = 0;
    private $dim = 0;            // The actual dimension. Shortcut of $dims[$currentDimID]; 
    private $maxDim = 0;         // Total count of dimensions
    private $changedLevel;      // highest level of changed group. Null when no change 
    // @property Groups $groups Holds array of group objects
    public $groups;
    // @property MajorProperties $mp Holds major properties to be passed to cumulator objects
    public $mp;
    // @property AbstractCollector $collector The master collector. All items
    // within this collector will cumumlated to higher level on group change.
    private $collector;
    // @property AbstractCollector $rc Collector for row counters
    public $rc;        // Collector for row counters
    // @property AbstractCollector $gc Collector for group counters
    public $gc;
    // @property AbstractCollector $total Collector of attributes to be cumulated
    public $total;
    private $target;           // Object which holds the methods to be called. Usally passed as $this
    private $_callOption = self::CALL_EXISTING;      // One of the prototype options above
    private $_prototype;       // prototype object  

    /**
     * Configurable mapping of methods to user method names.
     * The % sign in groupHeader and groupFooter names will be replaced by the 
     * group name or by the group level depending on $suffixByName.
     * fetchValues_n will be appended with the dimension ($dim) of data input.
     * Default will be set by config file or by config parameter.
     * @var array 
     */
    private $methods = [];       // Mapping internal to user method names.
    private $noDataActions = []; // action arrays when no data is given
    public $method;              // The current method (build by getCallable) 
    public $userConfig;          // user configuration. Not used by this application.
    // Level of lowest header called to be used as start index for footers.
    private $lowestHeader = 0;
    private $detailMethod;       // Indicator, class and method name to be called for details   
    private $initalized = false;
    private $needFooter = false;

    /**
     * Load parameter from config file and save given parameters.
     * @param object $owner Object which holds the methods to be called. Usually passed as $this.
     * @param array|null $config Dynamic configuration to replace defaults set in config.php file.
     */
    public function __construct($owner, array $config = null) {
        $this->target = $owner;
        $conf = Factory::configurator($config);
        $this->groups = new Groups($conf->buildMethodsByGroupName);
        $this->methods = $conf->methods;
        $this->userConfig = $conf->userConfig;
        $this->initialize();
        return $this;
    }

    private function initialize() {
        $this->mp = Factory::properties();
        $this->collector = Factory::collector();
        $this->mp->rc = $this->rc = Factory::collector();
        $this->collector->addItem($this->mp->rc, 'rc');
        $this->mp->gc = $this->gc = Factory::collector();
        $this->collector->addItem($this->mp->gc, 'gc');
        $this->mp->total = $this->total = Factory::collector();
        $this->collector->addItem($this->mp->total, 'total');
        $this->dims[] = $this->dim = new Dimension();
        $this->dim->addClosure = function($row, $rowKey) {
            $this->addValues($row, $rowKey);
        };
    }

    /**
     * 
     * @param int $dim
     * @param type $data Data points to the attribute of row which should be an 
     * iterable or is a callable.
     * @param bool $isData
     */
    public function data($data = null, $method = null) {
        if ($data === null && $method === null) {
            throw new \Exception('$data or $method must be given.');
        }
        if ($data !== null && $method !== null) {
            throw new \Exception('Only one of $data or $method parameters are allowed.');
        }
        // Call method in owner class when not specified
        if ($method !== null && !is_array($method)) {
            $method = [$this->owner, $method];
        }
        $dim = new Dimension($data, $method);
        $dim->addClosure = function($row, $rowKey) {
            $this->addValues($row, $rowKey);
        };
        $this->dims[] = $dim;
        $this->maxDim ++;
        return $this;
    }

    public function group($name, $attribute) {
        $dim = end($this->dims);
        $dim->groupAttr[$name] = $attribute;
        $group = $this->groups->newGroup($name, $this->maxDim);
        $this->setGroupActions($group);
        $this->gc->addItem(Factory::cumulator($this->mp, $group->level - 1, Factory::XS), $group->level);
        return $this;
    }

    public function sum($name, $dataAttribute, ?int $typ = Factory::REGULAR, ?int $maxLevel = null) {
        $typ = ($typ) ?? Factory::REGULAR;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $cum = Factory::cumulator($this->mp, $maxLevel, $typ);
        $this->total->addItem($cum, $name);
        if ($dataAttribute !== false) {
            $dim = end($this->dims);
            $value = $this->prepareSumAttribute($name, $dataAttribute, $dim);
            $dim->addCmd .= '$this->total->items[' . $this->wrapName($name) . "]->add($value);\n";
        }
        return $this;
    }

    public function sheet($name, $dataAttribute, $keyAttribute, ?int $typ = Factory::XS, $fromKey = null, $toKey = null, $maxLevel = null) {
        $typ = ($typ) ?? Factory::XS;
        $maxLevel = $this->checkMaxLevel($maxLevel);
        $cum = Factory::sheet($this->mp, $maxLevel, $typ, $fromKey, $toKey);
        $this->total->addItem($cum, $name);
        if ($dataAttribute !== false) {
            $dim = end($this->dims);
            $key = $this->prepareSumAttribute($name, $keyAttribute, $dim, 'key');
            $value = $this->prepareSumAttribute($name, $dataAttribute, $dim, 'data');
            $dim->addCmd .= '$this->total->items[' . $this->wrapName($name) . "]->add($value, $key);\n";
        }
        return $this;
    }

    private function prepareSumAttribute($name, $attribute, $dim, $attrTyp = null) {
        $wrkAttrTyp = ($attrTyp === null) ? '' : "['$attrTyp']";
        if ($attribute instanceof \Closure) {
            $dim->sumClosures[$name][$attrTyp] = $attribute;
            return '$this->dim->sumClosures[' . $this->wrapName($name) . ']' . $wrkAttrTyp . '($row, $rowKey)';
        } else {
            return '$row<<<' . $this->wrapName($attribute) . '>>>';
        }
    }

    private function wrapName($name) {
        return (is_string($name)) ? "'$name'" : $name;
    }

    private function checkMaxLevel($maxLevel = null): int {
        $dimMaxLevel = $this->groups->maxLevel;
        if ($maxLevel === null) {
            $maxLevel = $dimMaxLevel;
        } elseif ($maxLevel > $dimMaxLevel) {
            throw new exception("MaxLevel $maxLevel must be equal or less maxLevel of dim($dimMaxLevel).");
        }
        return $maxLevel;
    }

    /**
     * Set fromLevel and lastLevel on dims, instantiate row counter and
     * eventually call init and totalHeader methods.
     */
    private function finalInitializion() {
        $totalGroupCount = 0;
        foreach ($this->dims as $dimID => $dim) {
            $noOfGroups = count($dim->groupAttr);
            if ($noOfGroups > 0) {
                $dim->fromLevel = $totalGroupCount + 1;
                $totalGroupCount += $noOfGroups;
                $dim->lastLevel = $totalGroupCount;
            } else {
                $dim->fromLevel = $totalGroupCount;
                $dim->lastLevel = $totalGroupCount;
            }
            $this->rc->addItem(Factory::cumulator($this->mp, $dim->lastLevel, Factory::XS, $dimID));
            $this->noDataActions[$dimID] = $this->getCallable('noData_n', Helper::getConfigValue($dimID, $this->methods['noData_n']));
        }
        $this->detailMethod = $this->getCallable('detail', $this->methods['detail']);
        $this->callMethod('init');
        $this->callMethod('totalHeader');
        // init and report headers are done. Array elements are no longer needed.
        unset($this->methods['init'], $this->methods['totalHeader']);
    }

    /**
     * Detect the type of $method and build a callable out of it.
     * Decision is made in combination with the current callOption.
     * @param string $key The key of config methods array. 
     * @param mixed $method The derived method name (% replaced by dim or group)
     * @return array First array element indicates the action typ.
     * Second element is the method key(given be $key). 
     * Third element has the action to be taken. This might be a closure,
     * an callable array or a string. When the prototype class will be called
     * it's always the method key. In this case an additional element will be
     * appended which has the method name which would have been called.
     */
    private function getCallable($key, $method): ?array {
        // Call prototype regardless of the type. Prototype method is key
        if ($this->_callOption === self::CALL_ALWAYS_PROTOTYPE) {
            if ($method[0] >= self::CALLABLE) {
                return [$method[0], $key, [$this->_prototype, $key], $method[1]];
            } else {
                // string or closure
                // increase type by 10 to force always call the callable
                return [$method[0] + 10, $key, [$this->_prototype, $key]];
            }
        }
        // String, closure or array[class, method]
        if ($method[0] !== self::METHOD) {
            return [$method[0], $key, $method[1]];
        }
        // Normal method to be called in $target
        if ($this->_callOption === self::CALL_ALWAYS || method_exists($this->target, $method[1])) {
            return [$method[0], $key, [$this->target, $method[1]], $method[1]];
        }
        // Call protoype
        if ($this->_callOption === self::CALL_PROTOTYPE) {
            return [$method[0], $key, [$this->_prototype, $key], method[1]];
        }
        // no action is required
        return null;
    }

    /**
     * Set call option and instantiate prototyp object.
     * The call option defines which methods will be called and if they are
     * called in the $owner or in the prototype class.
     * Re-assign classes to be called for detail method and methods depending
     * on level or dimension. 
     * @param int $callOption One of the CALL_x constants.
     * @throws Exception
     */
    public function setCallOption(int $callOption) {
        if ($callOption < 0 || $callOption > 3) {
            throw new Exception('Invalid call option');
        }
        if ($callOption >= self::CALL_PROTOTYPE) {
            $this->_prototype = new Prototype($this);
        }
        $this->_callOption = $callOption;
        // Re-assign the classes in which methods will be called
        $this->detailMethod = $this->getCallable('detail', $this->methods['detail']);
        foreach ($this->groups->groups as $group) {
            $this->setGroupActions($group);
        }
        foreach ($this->noDataActions as $key => &$noAction) {
            $noAction = $this->getCallable('noData_n', Helper::getConfigValue($key, $this->methods['noData_n']));
        }
        return $this;
    }

    /**
     * Get prototype data for the current method.
     * @return string A html formatted table with some information related to
     * the last called method.
     * Valid for all methods declared in config with exception of 'fetchValues'
     * methods.
     */
    public function prototype(): string {
        if (!isset($this->_prototype)) {
            $this->_prototype = new Prototype($this);
        }
        // magic() detects the actual method.
        return $this->_prototype->magic();
    }

    /**
     * Prepare the actions for group headers and footers.
     * @param Group $group The group object for which the actions will be 
     * prepared
     */
    private function setGroupActions(Group $group): void {
        $key = $this->groups->getMethodNameReplacement($group);
        $group->headerAction = $this->getCallable('groupHeader', Helper::getConfigValue($key, $this->methods['groupHeader']));
        $group->footerAction = $this->getCallable('groupFooter', Helper::getConfigValue($key, $this->methods['groupFooter']));
    }

    /**
     * Call simple methods like init, totalheader and their conterparts
     * Those functions are called only once.
     * @param string $key The key of $this->methods
     */
    private function callMethod(string $key): void {
        $method = $this->getCallable($key, $this->methods[$key]);
        if ($method) {
            $this->method = $method;
            $this->output .= ($method[0] === self::STRING) ? $method[2] : $method[2]();
        }
    }

    /**
     * Process all data or just a subset (chunk) of all data rows.
     * When multidimensional data is used this function is called recursive.
     * @param iterable|null $data The data set to be processed
     * Can be the whole set or just a subset (chunk) of the set. Passing subsets
     * might reduce the amount of required memory. 
     * 
     * @param bool $finalize When true the end() method will be called after $data 
     *                       of the fist dimension ($dim = 0) has been processed.
     *                       When false this method might be called again with
     *                       other chunks of data.
     *                       To finalize the job $finalize need be true or end()
     *                       method must be called. 
     * @return string|null Result of end() when finalize is true or null when
     * finalize is false.
     */
    public function run(?iterable $data, bool $finalize = true) {
        if (!$this->initalized) {
            $this->finalInitializion();
            $this->initalized = true;
        }
        if (isset($data)) {
            foreach ($data as $rowKey => $row) {
                $this->next($row, $rowKey);
            }
        } elseIf ($this->currentDimID > 0) {
            $this->noData_n();
        }
        if ($this->currentDimID === 0 && $finalize) {
            return $this->end();
        }
    }

    /**
     * Handles a single row. Is called from run() or directly on user request.
     * @param array|object $row Data row to be processed.
     * @param string|int|null $rowKey Optional key of $row. Defaults to null.
     */
    public function next($row, $rowKey = null): void {
        $this->handleRow($row, $rowKey);
        if ($this->currentDimID < $this->maxDim) {
            $this->handleDimension($row, $rowKey);
            // Detail method can't be a string
        } elseif ($this->detailMethod) {
            $this->output .= ($this->detailMethod[2])($row, $rowKey);
        }
    }

    /**
     * Handle actions related to a row. 
     * 
     * Detect if a group has changed within the current dimension.
     * The level of highest changed group is stored at $this->changedLevel.
     * To detect a group change the values of group fields from current row 
     * are compared with values from previous row within the same dimension.
     * 
     * When group has changed header and footer actions will be called.
     * 
     * Important:
     * Coming back from a dimension doesn't force a group change.
     * Usually a group change happens because value of grouping attributes 
     * are not equal. When input data is not normalized declare an
     * additional group attribute to force a group change.
     * 
     * On first call of a new dimension the dimension will be initialized. 
     * @param array|object $row The current row.
     * @param int | string | null $rowKey The key of $row. Might not be given when
     * next() is called not from run().
     */
    private function handleRow($row, $rowKey): void {
        $indexedValues = [];
        if ($row instanceof \Object) {
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
            ($this->dim->addClosure)($row, $rowKey);
            return;
        }
        // Group has changed. Calculate index of the highest changed group.
        $changedLevelInDim = key($diffs);
        $this->changedLevel = $changedLevelInDim + $this->dim->fromLevel;
        $this->dim->groupValues = $indexedValues;
        ($this->needFooter) ? $this->callFooterMethods($this->changedLevel) : $this->needFooter = true;
        $this->dim->row = [$rowKey, $row];
        ($this->dim->addClosure)($row, $rowKey);
        // Group values from dim get active after handling footers. 
        $this->groups->setValues($this->dim->fromLevel, $indexedValues);
        // Call Header methods;
        $this->lowestHeader = $this->dim->lastLevel;
        $this->mp->level = $this->changedLevel;
        $groupValues = array_slice($indexedValues, $changedLevelInDim);
        foreach ($groupValues as $groupValue) {
            $this->gc->items[$this->mp->level]->inc();
            $this->callHeadersAndFooters('headerAction', $groupValue);
            $this->mp->level++;
        }
        $this->method = $this->detailMethod;
    }

    /**
     * Call the footer methods
     * Loop from lowest header level up to changed level.
     * When $this->lowestHeader < $this->changedLevel then no footers will be called.
     * This is especially true for new dimensions. 
     * @param int $changedLevel The group level where a group has changed
     */
    private function callFooterMethods(int $changedLevel): void {
//        $footerStartTime = microtime(true);
        $groupValues = array_reverse(array_slice($this->groups->values, $changedLevel));
        $this->mp->level = $this->lowestHeader;
        foreach ($groupValues as $groupValue) {
//            if ($this->currentDimID !== $this->groups->groups[$this->mp->level]->dim){
//                echo '<br>Dim not same for ' .$groupValue. ' Current = ' .$this->currentDimID .
//                        ' GroupDim = ' . $this->groups->groups[$this->mp->level]->dim;
//            }
            $this->callHeadersAndFooters('footerAction', $groupValue);
            $this->collector->cumulateToNextLevel();
            $this->mp->level--;
        }
//        $footerEndTime = microtime(true);
//        $this->footerUsedTime += $footerEndTime - $footerStartTime;
    }

    /**
     * Call a header or footer method using this arguments:
     * $value  The current group value for the called level
     * $row    The current row read belonging to the level to be called
     * $rowkey The key of the $row
     * $dimID  The ID of related dimension
     * @param string $action Group action to be called (headerAction or footerAction).
     * This is the attribute name in group class
     * @param mixed $groupValue The value belonging to the current group. 
     */
    private function callHeadersAndFooters($action, $groupValue): void {
        $group = $this->groups->groups[$this->mp->level];
        $method = $group->$action;
        if ($method) {
            $this->method = $method;
            if ($method[0] === self::STRING) {
                $this->output .= $method[2];
            } else {
                $group = $this->groups->groups[$this->mp->level];
                $dim = $group->dim;
                $this->output .= $method[2](
                        $groupValue   // active groupValue
                        // Rows index belongs to the current group and not to $this->currentDimID
                        , $this->dims[$dim]->row[1]
                        , $this->dims[$dim]->row[0]
                        , $dim
                );
            }
        }
    }

    /**
     * Create a closure which handles all add() methods for attributes
     * to be cumulate.
     * Closure als increments row counter.
     * Method will be called only once per dimension.
     * @param mixed $row The data row
     * @param mixed $rowKey The key of data row
     * @return void
     */
    private function addValues($row, $rowKey): void {
        $cmd = $this->prepareCmd((is_array($row)), $this->dim->addCmd);
        // Build the closure which adds row counter and sum attributes for
        // the actual level.
        eval('$this->dim->addClosure = function($row, $rowKey){'
                . '$this->rc->items[$this->currentDimID]->inc();' . "\n"
                . $cmd . '};');
        ($this->dim->addClosure)($row, $rowKey);
    }

    private function prepareCmd(bool $isArray, $command) {
        if ($isArray) {
            $command = str_replace('<<<', '[', $command);
            $command = str_replace('>>>', ']', $command);
        } else {
            $command = str_replace(["<<<'", "<<<"], '->', $command);
            $command = str_replace(["'>>>", ">>>"], '', $command);
        }
        return $command;
    }

    /**
     * *************************************************************************
     * Following methods are used to handle Dimensions                         *
     * ********************************************************************** */

    /**
     * In combination with $this->run() its a recursive function.
     * Dimension is incremented at begin of this method, then run() or a user
     * defined method is called. 
     * At end of this method the dimension decrements.
     * @param array|object $row The current data row. 
     * @param mixed $rowKey The key of $row
     */
    private function handleDimension($row, $rowKey): void {
        $this->currentDimID ++;
        $this->dim = $this->dims[$this->currentDimID];
        $this->dim->groupValues = [];
        if ($this->dim->data !== null) {
            if ($this->dim->data instanceof \Closure) {
                $this->run($this->dim->data($row, $rowKey, $this->dims->current));
            } elseif ($row instanceof \Object) {
                (isset($row->{$this->dim[$data]})) ? $this->run($row->{$this->dim[$data]}) : $this->run(null);
            } else {
                (isset($row[$this->dim->data])) ? $this->run($row[$this->dim->data]) : $this->run(null);
            }
        } else {
            // Call method which must call run() or next() methods.
            $dim->method($row, $rowKey, $this->dims->current);
        }
        $this->currentDimID --;
        $this->dim = $this->dims[$this->currentDimID];
    }

    /**
     * Run methods to finalize the job
     * @return string output. The collected return values by called owner methods
     */
    public function end() {
        if (isset($this->methods['init'])) {
            // No data was given. Init and totalHeader are not done 
            $this->callMethod('init');
            $this->callMethod('totalHeader');
            $this->callMethod('noData');
        } else {
//            $this->changedLevel = 1;
            $this->callFooterMethods(1);
            $this->mp->level = 0;
        }
        $this->callMethod('totalFooter');
        $this->callMethod('close');
        return $this->output;
    }

    /**
     * Handle no data given at dimension greater than 0
     */
    private function noData_n(): void {
        $method = $this->noDataActions[$this->currentDimID];
        if ($method) {
            $this->method = $method;
            $this->output .= ($method[0] === self::STRING) ? $method[2] : $method[2]($this->currentDimID);
        }
    }

    /**
     * ******************************************************************************************
     * Following methods are extra sugar for target object. Not needed for program flow.        *
     * **************************************************************************************** */
    public function getLevel(): int {
        return $this->mp->level;
    }

    /**
     * Returns the boolean wether the currently processed function is the first
     * time called within the next higer level or not.
     * For detail level rowCount() is checked while for other levels
     * the inGroupCount() is used.
     * @return bool True when it is the first, else false.
     */
    public function isFirst(): bool {
        if ($this->method[1] !== 'detail') {
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
        if ($this->method[1] !== 'groupFooter') {
            throw new Exception('isLast() can only be answered in groupFooters');
        }
        return ($this->mp->level !== $this->changedLevel);
    }

    public function getRow(int $dim = null) {
        return $this->dims[($dim) ?? $this->currentDimID]->row[1];
    }

    public function getRowKey(int $dim = null) {
        return $this->dims[($dim) ?? $this->currentDimID]->row[0];
    }

    /**
     * Get all active values. In header and detail functions these are the values 
     * out of the current data row. In footer functions the values of the 
     * previous row are returned. 
     * @return array The values out of the data row in relation to the current 
     *               status of the report.
     */
    public function groupValues() {
        return $this->groups->values;
    }

    /**
     * Get the active value for a given level. See getGroupValues for more details.
     * @param int $level Get the group value for this level.
     * @return mixed The requested value.
     */
    public function getGroupValue($group = null) {
        if (is_string($group)) {
            $key = $this->groupLevel($group);
        } else {
            $key = $group ?? $this->mp->level;
        }
        return $this->groups->values[$key];
    }

    /**
     * Get the associated group level of a group name.
     * When $groupName is not set the current level will be returned.
     * @param integer|string $groupName The name of the grouping field
     * @return integer The associated level from the groupName.
     */
    public function groupLevel($groupName) {
        return $this->groups->groupsX[$groupName]->level;
    }

    /**
     * Get all group names.
     * @return array The group names. Key is the associatet level.
     */
    public function groupNames(): array {
        return $this->groups->groupNames();
    }

    /**
     * Get the group name of a given level. 
     * @param int $level The level for which the group name will be returned.
     * Defaults to the current level.
     * @return string | numeric The group name of the requested level.
     */
    public function getGroupName(int $level = null) {
        $key = $level ?? $this->mp->level;
        return $this->groups->groups[$key]->groupName;
    }

}
