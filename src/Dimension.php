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
    public $isLastDim = false;  // True for the last dimension
    
    public $fromLevel;  // Level of first group within dimension
    public $lastLevel;  // Level of last group within dimension
    
    // Data source for next dimension
    public $dataSource;
    // Source for values to detect group changes. Declared by group(). Key is group name.
    public $groupSource = [];
    // Array of optional additional parameter to be passed to external methods
    public $parameters;
    public $noDataParam; // Parameter for noData action.
    public $noGroupChangeParam; // Parameter for noGroupChange action.
    // Action to be performed on each data row in dimimension which is not the 
    // last dimension. For last dimension rows detail action is performed. 
    public $rowDetail;
    // Runtime action to be performed when no data are given via $data. Has type and action.
    public $noDataAction = [];
    // Runtime action to be performed for each data row.
    public $detailAction = [];
    // Runtime action to be performed when no group change was triggered.
    public $noGroupChangeAction = [];
    
    // Attributes to be summarized. Key is name of calculator or sheet, value 
    // is info how where to find values(s) from row.
    public $calcs = [];
    // Array having only info about type (string, array, closure) of the groupAttr
    // to avoid expensive is_array() calls.
    // Attributes to be summarized. Key is sum name, value is info how to extract value from row.
   
    public $row;           // Current data row
    public $rowKey;        // Key of current data row
    
    public $groupValues = [];   // Array of group values to detect group change
    public $dataHandler;        // Object which handles methods related to the type of data row.

    public function __construct(int $id, $target) {
        $this->id = $id;
        $this->nextID = ++ $id;
        $this->dataHandler = new UnknownDataHandler($this, $target);
    }

    /**
     * 
     * @param mixed $data Instruction how data has to be extracted from row 
     * @param null|array $noDataParam No data parameter transfered to an array
     * with action type as first array element. 
     * @param null|array $dataParam Data parameter transfered to an array
     * with action type as first array element.   
     */
    public function setParameter($source = null, array $noDataParam = null, array $rowDetail = null, array $noGroupChangeParam = null, $parameters = []) {
        $this->dataSource = $source;
        $this->noDataParam = $noDataParam;
        $this->rowDetail = $rowDetail;
        $this->noGroupChangeParam = $noGroupChangeParam;
        $this->parameters = $parameters;
    }

   }
