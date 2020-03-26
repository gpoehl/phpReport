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

    /** @var Array of actions. Key is action key, value is an array having action 
     * type and action. The % sign in action is replaced by the dimID 
     */
    public ?array $actions;

    /** @var prepared noData action to be executed */
    public ?array $runtimeNoDataAction = [];

    /** @var prepared noGroupChange action to be executed */
    public ?array $runtimeNoGroupChangeAction = [];

    /** @var prepared detail action to be executed */
    public ?array $runtimeDetailAction = [];
    
    public $row;           // Current data row
    public $rowKey;        // Key of current data row
    public $groupValues = [];   // Array of group values to detect group change
   public $groupNames = [];     // Array of group names. Not indexed 
    public $dataHandler;        // Object which handles methods related to the type of data row.

    public function __construct(int $id, $dataHandler, $source = null, $target = null, ?array $actions = null, ...$params) {
        $this->id = $id;
        $this->nextID = ++$id;
        $this->isLastDim = ($source === null);
        $this->dataHandler = $this->getDataHandler($dataHandler, $source, $params);
        $this->target = $target;
        $this->actions = $actions;
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
