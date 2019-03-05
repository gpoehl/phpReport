<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Formulars to get results out of calculated attributes, group- and row counters.
 * All methods are working on a 'running base'. So results are available
 * at any time and not only on group levels.
 * @author Guenter
 */
class Formular extends Calculator {

    public $dim = 0;            // The actual dimension 
    
    public $detailLevel;        // Level of detail group  
    // @property Dimensions $dims
    public $dims;               // dimensions object reference which holds array of dimension objects.
    // @property Groups $groups
    public $groups;             // groups object which holds array of group objects.
    public $attrLevel = [];  // array holding names of attributes to be cumulated 
    // with their lowest level.
    private $colKeys;           // Column name is key of attributes to be cumulatet. Value is the number of column.
    private $colNames;          // Column number is key of attributes to be cumulatet. Name is Value.

    public function __construct() {
        parent::__construct();
    }

    /**
     * Add values to calculated attributes. This method can be called by the 
     * owner to set an initial value or to manage values beyond the normal way.
     * 
     * @param string $name The attribute name to which the $value will be added. 
     *                    When this attribute has not been defined before
     *                    (e.g. by the 'calculate' parameter of fetch_values
     *                    method) it will be inititalized from grand total
     *                    level to $lowestLevel.
     * @param numeric $value The value to be added to $name.
     * @param int $level The level where the value will be added to. Defaults to
     *                   the current level. On detail level it defaults
     *                   to the lowest group level.
     *                   
     * @param bool $addCounter When true the non zero and non null counters
     *                         will be incremented depending of the value.
     *                         When false the counters will not be incremented.
     *                         Default is true. 
     *  @param int|null $lowestLevel When the attribute of $name has to be initalized
     *                  the lowest level can be spezified. Defaults to the 
     *                  lowest level of the current dimension.
     *                  
     */
    public function add(string $name, $value, int $level = null, bool $addCounter = true, int $lowestLevel = null): void {

        $level = ($level) ?? (($this->level === $this->detailLevel) ? $this->groups->maxLevel : $this->level);
        if ($lowestLevel === null) {
            $lowestLevel = $this->groups->groups[$level]->dim;
        }
        parent::add($name, $value, $level, $addCounter, $lowestLevel);
    }

    /**
     * *************************************************************************
     * Methods to retrieve calculated values
     * ************************************************************************ */

    /**
     * Get the cumulated value (total) of an totalized column or an range of 
     * totalized columns within a specific level. 
     * When this function is called outside of a footer function then
     * the sum represents a running total.
     * @param string|array|null $column The name of the column to get the sum
     * from. If $column is null it defaults to an array where the first column
     * name is the first element and the last column the second element.
     * If an array is given then it should contain the name of the starting 
     * as the first element and the ending column as the second element. 
     * @param int $level The requested cumulation level of $column.
     *                   Defaults to the current level. 
     * @param bool $asArray When $column is an array and $asArray is true (default)
     *                      an array will be returned where the column name is
     *                      key and value ist the sum. 
     *                      When $asArray is false an scalar value of all seleced
     *                      columns will be returned.
     * @return int|array The cumulated value of a column or range of columns.
     */
    public function sum($column, ?int $level = null, bool $asArray = true) {
        return $this->totals[$column]->sum(($level)??$this->level);
    }
     public function sumx($column = null, ?int $level = null, bool $asArray = true) {
        return $this->getSum(self::VALUE, $column, $level, $asArray);
    }

    /**
     * Get the number of values for a column having a value of not null.
     * @param mixed $column See details at sum(). 
     * @param int $level See details at sum().
     * @return int|array Counter of how many values in a column had a value
     *                   being not null.
     */
    public function nnCount($column = null, int $level = null) {
        return $this->getSum(self::NOTNULL, $column, $level);
    }

    /**
     * Get the number of values for a column having a value of not zero.
     * @param mixed $column See details at sum(). 
     * @param int $level See details at sum().
     * @return int|array Counter of how many values in a column had a value
     *                   being not zero.
     */
    public function nzCount($column = null, int $level = null) {
        return $this->getSum(self::NOTZERO, $column, $level);
    }

    /**
     * Get the number of rows processed per dimension related to a given level.
     * On level = 0 rowCount has the total number of read rows while on other
     * levels rowCount has the number of rows belonging to the actual data value
     * of the level.
     * 
     * Number of rows is a simplified term. In fact it is the number of entries
     * processed from the given input data.
     * 
     * @param int $level     Get row counter of the requested group level.
     *                       Defaults to the current level. On detail level it 
     *                       defaults to the level above. 
     * @param int|null|array $dim Get row counter of the given dimension. Defaults
     *                       to the dimension belonging to the current level. 
     *                       When $dim is an array it should contain the first
     *                       requested dim as the first element and the last
     *                       requested dim as the second element. 
     *                       First element defaults to 0 while the last element
     *                       defaults to the dim related to the current dimension.
     *                       Please note the last dimension can be obtained only
     *                       when at least one row of each dimension has been read.
     *                       Till then last represents only the last dimension 
     *                       read so far.
     * @param bool $asArray  When $dim is an array and $asArray is true (default)
     *                       an array will be returned where the dimension is
     *                       key and the counter the value. 
     *                       When $asArray is false an scalar value of all selected
     *                       dimensions will be returned.
     * @return int|array     The cumulated counter of a dimension or range of 
     *                       dimensions.
     */
    public function rowCount(int $level = null, $dim = null, $asArray = true) {
        $level = ($level) ?? (($this->level === $this->detailLevel) ? $this->groups->maxLevel : $this->level);
        if (is_array($dim)) {
            $from = (isset($dim[0])) ? $dim[0] : 0;
            $to = (isset($dim[1])) ? $dim[1] : (($level === 0) ?
                    array_key_last($this->dims->dims) : $this->groups->groups[$level]->dim);
            if ($asArray) {
                for ($i = $from; $i <= $to; $i++) {
                    $sum[$i] = $this->runningTotal($this->rowCounter, $i, $level, $i - 1);
                }
                return $sum;
            }
            // Get scalar value
            $sum = 0;
            for ($i = $from; $i <= $to; $i++) {
                $sum += $this->runningTotal($this->rowCounter, $i, $level, $i - 1);
            }
            return $sum;
        }
        if ($dim === null) {
            if ($this->level === 0) {
                $dim = 0;
            } elseif ($this->level === $this->detailLevel) {
                $dim = $this->dims->maxDim;
            } else {
                $dim = $this->groups->groups[$this->level]->dim;
            }
        }

        // Only one column is requested 
        return $this->runningTotal('rc', $dim, $level, $this->dims->dims[$dim]->lastLevel);
    }

    /**
     * Get the counter how often a child group exists in a higer group level
     * or the actual number of child occurence in a higher level. 
     * groupCount method looks up from a group to higher level(s) while  
     * childGroupCount looks down to lower levels. 
     * @param int $parentLevel The cumulated level. Defaults to the level above
     * the child level.
     * @param int $childLevel The group level which exists in a higer level.
     * Defaults to the current level.
     * @return int The number how often a child level occurs in the parent level  
     */
    public function groupCount(int $parentLevel = null, int $childLevel = null): int {
        $childLevel = $childLevel ?? $this->level;
        $parentLevel = $parentLevel ?? $childLevel - 1;
        return $this->getGroupCount($childLevel, $parentLevel);
    }

    /**
     * Get the counter of child group occurrences a parent level owns.
     * While groupCount methodlooks from a group to the higher level 
     * childGroupCount looks down to lower levels. 
     * @param int|null $childLevel The group level for which the counter is requested.
     * Defaults to the level below $parentLevel.
     * @param int|null $parentLevel The level which owns the child level. Defaults to
     * the current level.
     * @return int The number how often a child level occurs in the parent level 
     */
    public function childGroupCount(int $childLevel = null, int $parentLevel = null): int {
        $parentLevel = $parentLevel ?? $this->level;
        $childLevel = $childLevel ?? $parentLevel + 1;
        return $this->getGroupCount($childLevel, $parentLevel);
    }

    private function getGroupCount(int $childLevel, int $parentLevel) {
        if ($childLevel <= $parentLevel) {
            throw new InvalidArgumentException("groupCount: Child level ($childLevel) "
                    . "must be greater then parent level ($parentLevel).");
        }
        return $this->runningTotal('gc', $childLevel, $parentLevel, $childLevel - 1);
    }

    /**
     * Counter of different group values in parent groups belonging to a group.
     * This method returns same data as the childGroupCount method but from a 
     * different view (from child to parent). 
     * @param int|null|array $level The group counter to be selected.
     *                              Defaults to the group above the current level.
     *                              When $group is an array it should contain the
     *                              start group as the first parameter (defaults
     *                              to the current level + 1) and the end group
     *                              as the second parameter (defaults to the
     *                              total number of defined groups). 
     *                              The returned result is an array where key is
     *                              the group and counter is the value. 
     * @param int|null $parentLevel The level on which the group counter will be
     *                              returned. Defaults to the current level. 
     * @return int|array            Integer or array of group counters depending
     *                              on $level and $parentLevel.
     * @throws InvalidArgumentException
     */
    public function groupCount1($level = null, int $parentLevel = null) {
        $level = $level ?? $this->level;
//        if (is_array($group)) {
//            $from = (isset($group[0])) ? $group[0] : $this->level + 1;
//            $to = (isset($group[1])) ? $group[1] : $this->groups->count;
//            for ($i = $from; $i <= $to; $i++) {
//                $sum[$i] = $this->runningTotal('gc', $i, $level, $i - 1);
//            }
//            return $sum;
//        }
        // Only one parent group is requested. Default is next higher level 
        $parent = $parentLevel ?? $level - 1;
        return $this->runningTotal('gc', $parent, $level, $level + 1);
    }

    /**
     * Counter of different group values in child groups belonging to a group.
     * This method returns same data as the groupCount method but from a 
     * different view (from parent to child). 
     * @param int $level The level which owns child level(s). Defaults to 
     * the current level. Must not be the lowest defined group level or the
     * detail level. 
     * @param int $childLevel The group level of the child group. Defaults to the 
     * level below $level. 
     * @return int The number of different group values in $childLevel belonging
     * to the level given in $level.
     * @throws InvalidArgumentException
     */
    public function childGroupCount1(int $level = null, int $childLevel = null): int {
        $level = $level ?? $this->level;
        $child = $childLevel ?? $level + 1;
        if ($child <= $level) {
            throw new InvalidArgumentException("childGroupCount: ChildLevel"
                    . " ($child) must be greater then level ($level).");
        }
        if (!empty($this->detailLevel) && $childLevel >= $this->detailLevel) {
            throw new InvalidArgumentException("childGroupCount: ChildLevel"
                    . " ($child) must be less then detail level ($this->detailLevel).");
        }
        return $this->runningTotal('gc', $child, $level, $level - 1);
    }

    /*
     * *************************************************************************
     * Helper functions to assist statistical functions
     * ************************************************************************* 
     */

    /*
     * Get the relevant data out of the internal array $totals. This function
     * should be called by all statistical functions invoked by a user program.
     * @param string $type is the internal key to differentiate the cumulated data.
     * @param string|array|null $column is the name of the column which 
     * should be returned. If the value is null then all columns are returned.
     * If it is an array it should contain only the names of the first and the
     * last column out of the column list. All columns in between those names
     * will be returned
     * @param int $level is the grouping level for which the column(s) 
     * should be returned. The returning value(s) is / are always running totals.
     * When Null the current level or, when the current level equals the detail
     * level, the level above the detail level will be used. 
     * @return numeric | array The requested value out of the $totals array
     * or an array containig all requsted colmuns where key is the column name
     * and value the corresponding value.
     */

    private function getSum($type, $column = NULL, int $level = Null, bool $asArray = true) {
        $level = ($level) ?? (($this->level === $this->detailLevel) ? $this->groups->maxLevel : $this->level);

        if ($column === null) {
            $columns = $this->colNames;
        } elseif (is_array($column)) {
            $from = $this->colKeys[$column[0]];
            $to = ($column[1] === null) ? null : $this->colKeys[$column[1]] - $from + 1;
            $columns = array_slice($this->colNames, $from, $to);
        } else {
            // Only one column is requested 
            return $this->runningTotal($type, $column, $level, $this->calcAttributes[$column][0]);
        }
        if ($asArray) {
            // Get sum of all attributes as array
            foreach ($columns as $column) {
                $sum[$column] = $this->runningTotal($type, $column, $level, $this->calcAttributes[$column][0]);
            }
        } else {
            foreach ($columns as $column) {
                $sum += $this->runningTotal($type, $column, $level, $this->calcAttributes[$column][0]);
            }
        }
        return $sum;
    }

    /**
     * Caluclate the (running) total of a given column. 
     * 
     * @param string $tye Same as key's in calcRowTotals
     * @param int | string $column The column name or child group level which 
     * will be cumulated.
     * @param int $level The requested group level 
     * @return numeric The sum of an specified column for the given level 
     */
    private function runningTotal1(array $counter, int $level, int $lowestLevel, $column) {
        $sum = 0;
        do {
            $sum += $counter[$level][$column];
            $level ++;
        } while ($level <= $lowestLevel);
        return $sum;
    }

    
    private function runningTotal($summarize, ...$keys) {
        return 0;
        $sum = 0;
        do {
            $sum += $summarize($keys); 
            $sum += $counter [$level] [$keys];
            $level ++;
        } while ($level <= $lowestLevel);
        return $sum;
    }

    /*
     * **********************************************************************************
     * Calculate and return diffent kind of averages
     * ********************************************************************************* 
     */

    /**
     * Calculate the average based on row counts.
     * @param mixed | [mixed] $column The column for which the average should 
     *                                 be returned. When column is an array the
     *                                 averages for all columns within this array
     *                                 will be returned as an array. 
     * @param int $level
     * @return numeric or [numeric]    The requested averages. If $column is an
     *                                 array the returned array keys represent
     *                                 the totalized attributes key's. 
     */
    public function avg($column = NULL, int $level = Null) {
        $divisor = $this->rowCount($level);
        $colSum = $this->sum($column, $level);
        if (is_array($colSum)) {
            foreach ($colSum as $column => $value) {
                $avg[$column] = $value / $divisor;
            }
            return $avg;
        }
        return $colSum / $divisor;
    }

    /**
     * Calculate the average of a column based on the number of values not 
     * being zero.
     * 
     * @param and @return is the same as for similar functions. $column
     */
    public function avgNZ($column = NULL, int $level = Null) {
        return $this->getAvgn(self::NOTZERO, $column, $level);
    }

    /**
     * Calculate the average of a column based on the number of values not 
     * being null.
     * @param and @return is the same as for similar functions. $column
     */
    public function avgNN($column = NULL, int $level = Null) {
        return $this->getAvgn('nn', $column, $level);
    }

    /**
     * Calculate the average based on the number of groups.
     * @param mixed $column
     * @param int $subGroup A group lower than the current group level.
     *                        Returns an average build on the sum of a totalized
     *                        column divided by the number of groups of an lower
     *                        lever. E.g. the average sales of all years.
     *                        Defaults to the next lever group. 
     * @param int $level
     * @return numeric The average of ..
     */
    public function avgGC($column = NULL, int $subGroup = null, int $level = Null) {
        $counterCol = $this->prepIndex($subGroup, $this->prepGroups, $level + 1);
        return $this->getAvg('gc', $counterCol, $column, $level);
    }

    /**
     * Calculate the average for calculated totals where for each totalized column
     * an divisor from the same index will be used (average on nn or nz counts).
     * 
     * @param string $typ
     * @param type $column
     * @param int $level 
     * @return [] | numeric An array with averages for all totalized columnof a given
     *                       group or foan numeric value of a given group field.
     *                       When the assoziated counter has a value of 0 then Null
     *                       will be returned. 
     */
    private function getAvgn(string $typ, $column = null, int $level = null) {
// Set level here to be used when getting nn or nz counter
        if (is_null($level)) {
            $level = $this->level;
        }
// get all sum's
        $colSum = $this->sum($column, $level);
//        echo '<br>getAvgn<br>';
//        \yii\helpers\VarDumper::dump($colSum,10,true);
        if (is_array($colSum)) {
// calculate for each column sum the average
            foreach ($colSum as $column => $value) {
                // get the nn or nz counter
                $devisor = $this->runningTotal($typ, $column, $level);
//                echo "<br>getAvgn devisor $devisor<br>";
                $avg[$column] = ($devisor === 0) ? null : $value / $divisor;
            }
            return $avg;
        }
// only one column is requested. Calculate the average
        $devisor = $this->runningTotal($typ, $column, $level);
//         echo "<br>getAvgn devisor $devisor<br>";
        return ($devisor === 0) ? null : $colSum / $devisor;
    }

    /*
     * *****************************************************************************************
     * Sum and averages on joined columns 
     * **************************************************************************************** */

    /**
     * Average. Cross sum of totalized attributes divided by the number of selected
     * totalized attributes
     * @param array $column
     * @param int $level 
     * @return numeric The caluclated average  
     */
    public function crossAvg(array $column = [null, null], int $level = null) {
        $wrk = $this->getFromTotals(self::VALUE, $column, $level);
        return array_sum($wrk) / count($wrk);
    }

    public function crossAvgNN($column = [null, null], int $level = null) {
        $wrk = $this->getFromTotals(self::VALUE, $column, $level);
        foreach ($wrk as $val) {
            $sum += $val;
            if ($val !== null) {
                $count ++;
            }
        }
        return ($count > 0) ? $sum / $count : null;
    }

    public function crossAvgNZ($column = [null, null], int $level = null) {
        $wrk = $this->getFromTotals(self::VALUE, $column, $level);
        foreach ($wrk as $val) {
            $sum += $val;
            if ($val !== 0) {
                $count ++;
            }
        }
        return ($count > 0) ? $sum / $count : null;
    }

}
