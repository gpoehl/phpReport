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

    public $fromLevel;  // Level of first group within dimension
    public $lastLevel;  // Level of last group within dimension
// Data source for next dimension
    public $source;
    // Additional variadic parameter list to be passed to external methods
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
    // Attributes to determine group changes. Given by group(). Key is group name.
    // Value has info how value is extracted from row. 
    public $groupAttr = [];
    //Attributes to be summarized. Key is sum name, value like in $groupAttr.
    public $sumAttributes = [];
    //Attributes to be summarized in sheets. Key is sheet name, value like in $groupAttr.
    public $sheetAttributes = [];
    public $row = [];          // Array Current row and rowKey
    public $groupValues = [];   // Array of group values to detect group change

    /**
     * 
     * @param mixed $data Instruction how data has to be extracted from row 
     * @param null|array $noDataParam No data parameter transfered to an array
     * with action type as first array element. 
     * @param null|array $dataParam Data parameter transfered to an array
     * with action type as first array element.   
     */
    public function setParameter($source = null, array $noDataParam = null, array $rowDetail = null, array $noGroupChangeParam = null, $parameters) {
        $this->source = $source;
        $this->noDataParam = $noDataParam;
        $this->rowDetail = $rowDetail;
        $this->noGroupChangeParam = $noGroupChangeParam;
        $this->parameters = $parameters;
    }

}
