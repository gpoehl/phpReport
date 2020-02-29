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
    public $isLastDim;  // Bool is this is the last dimension
    public $fromLevel;  // Level of first group within dimension
    public $lastLevel;  // Level of last group within dimension
    public $target;     // Default class for user methods called in dataHandler
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
    public $row;           // Current data row
    public $rowKey;        // Key of current data row
    public $groupValues = [];   // Array of group values to detect group change
    public $dataHandler;        // Object which handles methods related to the type of data row.

    public function __construct(int $id, $dataHandler, $source = null, $target = null, $noData = null, $rowDetail = null, $noGroupChange = null, ...$params) {
        $this->id = $id;
        $this->nextID = ++$id;
        $this->isLastDim = ($source === null);
        $this->dataHandler = $this->getDataHandler($dataHandler, $source, $params);
        $this->target = $target;
        $this->setConfigParam($noData, $this->noDataParam, 'noData_n');
        $this->setConfigParam($rowDetail, $this->rowDetail, 'detail_n');
        $this->setConfigParam($noGroupChange, $this->noGroupChangeParam, 'noGroupChange_n');
    }

    /**
     * 
     * @param type $dataHandler
     * @param type $source
     * @param type $params
     * @return object
     * @throws InvalidArgumentException
     */
    public function getDataHandler($dataHandler, $source, $params): object {
        switch (strtolower($dataHandler)) {
            case 'array':
                $dataHandler = ArrayDataHandler::class;
                break;
            case 'object':
                $dataHandler = ObjectDataHandler::class;
                break;
            default:
                if (!class_exists($dataHandler)) {
                    throw new \InvalidArgumentException("DataHandler $dataHandler does not exist.");
                }
        }
         return new $dataHandler($this, $source, $params);
    }

    private function setConfigParam($value, &$param, $configName) {
        if ($value !== null) {
            $param = Helper::buildMethodAction($value, $configName);
        }
    }

    /**
     * 
     * @param int $lastLevel
     * @return int
     */
    public function setFromAndLastLevel(int $lastLevel): int {
        $this->fromLevel = $this->lastLevel = $lastLevel;
        if ($this->dataHandler->numberOfGroups > 0) {
            $this->lastLevel = $this->fromLevel + $this->dataHandler->numberOfGroups;
            $this->fromLevel++;
        }
        return $this->lastLevel;
    }

}
