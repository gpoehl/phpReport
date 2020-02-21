<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */
declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Detect type of first data row in a dimension
 */
class UnknownDataHandler {
    /* @var $dim Dimension */
    public $dim;
    /* @var $target Target class set during instantiation of phpReport class */
    public $target;
    
     public function __construct(Dimension $dim, $target) {
        $this->dim = $dim;
        $this->target = $target;
    }

    /**
     * Get group values out of the current row.
     * This method is the first one called for each new Dimension. 
     * The type of data row will determined and the appropiate data handler
     * instantiated. This new data handler will we replaced in the dimension
     * object the be called by following data rows.
     * To return the requested values for the current row the new data handler will
     * be called.
     * @param mixed $row The row which has the group attributes.
     * @param int | string | null $rowKey The key of $row. 
     * @param Dimension Object which serves $groupAttr.
     * @return array Indexed array having the group values out of the given row.
     */
    public function getGroupValues($row, $rowKey): array {
        if (is_object($row)) {
            $handler = new ObjectDataHandler($this->dim, $this->target);
        } else {
            $handler = new ArrayDataHandler($this->dim ,$this->target);
        }
       $handler->dataType = $this->getSourceType($this->dim->dataSource);
      $handler->groups = $this->getSourceTypes($this->dim->groupSource);
//        $handler->calcs = $this->dim->calcs;
        $handler->dimID = $this->dim->id;

        $this->dim->dataHandler = $handler;
        return $handler->getGroupValues($row, $rowKey);
    }

    private function getSourceTypes($source) {
        $sourceTypes = [];
        foreach ($source as $name => $attr) {
            $sourceTypes[$name] = [$this->getSourceType($attr), $attr];
        }
        return $sourceTypes;
    }

    private function getSourceType($attr) {
        if ($attr instanceof \Closure) {
            return DataHandler::CLOSURE;
        }
        if (is_array($attr)) {
            return (count ($attr) === 1) ? DataHandler::METHOD : DataHandler::CLASSMETHOD;
        }
        return DataHandler::ATTRIBUTE;
    }

}
