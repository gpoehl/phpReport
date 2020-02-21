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
 * Methods related to data being objects
 */
class ObjectDataHandler extends DataHandler {

    /**
     * Get group values out of the current row
     * @param Object $row The row which has the group attributes.
     * @param int | string | null $rowKey The key of $row. 
     * @return array Indexed array having the group values out of the given row.
     */
    public function getGroupValues(Object $row, $rowKey): array {
        $values = [];
        foreach ($this->groups as list($type, $source)) {
            switch ($type) {
                case DataHandler::ATTRIBUTE:
                    $values[] = $row->$source;
                    break;
                case DataHandler::CLOSURE:
                    $values[] = $source($row, $rowKey, $this->dim->id);
                    break;
//            case DataHandler::METHOD:
//                    $values[] = $row->{$groupSource[0]}(... $dim->parameters);
//                default: // case DataHandler::CLASSMETHOD:
//                    $values[] = ($dim->dataSource)($row, $rowKey, $dim->id + 1, ... $dim->parameters);
            }
        }
        return $values;
    }

    /**
     * Add values to caluclate() or sheet() attributes
     * @param object $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     * @param array $total The itmes property of the total collector.
     */
    public function addValues(object $row, $rowKey, array $total): void {
        foreach ($this->dim->calcs as $name => list($type, $attr)) {
            switch ($type) {
                case DataHandler::ATTRIBUTE:         
                    $total[$name]->add($row->$attr);
                    break;
                case DataHandler::SHEETATTRIBUTES:  
                    // call add() with an array having one key=>value element
                    $total[$name]->add([$row->{$attr[0]} => $row->{$attr[1]}]);
                    break;
                case DataHandler::CLOSURE:        
                    $total[$name]->add($attr($row, $rowKey, $this->dimID));
                    break;
                default:         
                    // called method must return an scalar value for calculate()
                    // or an array for sheets.
                    $total[$name]->add($attr($row, $rowKey, $this->dimID));
            }
        }
    }

    /**
     * Get data for the next dimension 
     * @param mixed $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     * @return null|false|iterable The data for the next dimension
     */
    public function getDimData(object $row, $rowKey) {
        switch ($this->dataType) {
            case DataHandler::ATTRIBUTE:
                return $row->{$this->dim->dataSource};
            case DataHandler::CLOSURE:
                return ($this->dim->dataSource)($row, $rowKey, $this->dim->nextID, ... $dim->parameters);
            case DataHandler::METHOD:
                return $row->{$this->dim->dataSource[0]}(... $this->dim->parameters);
            default: // case DataHandler::CLASSMETHOD:
                return ($this->dim->dataSource)($row, $rowKey, $this->dim->nextID, ... $this->dim->parameters);
        }
    }

}
