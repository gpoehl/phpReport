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
 * Methods related to data rows being objects
 */
class ObjectDataHandler {

    use BaseDataHandler;

    /**
     * Get group values out of the current row.
     * When no groups are declared an empty array will be returned.
     * 
     * @param Object $row The current data row.
     * @param int | string | null $rowKey The key of $row. 
     * @return array Array of all group values from given $row.
     */
    public function getGroupValues(Object $row, $rowKey): array {
        $values = [];
        foreach ($this->groupValueSources as $source) {
            $values[] = ($source[0] === Helper::ATTRIBUTE) ? $row->{$source[1]} :
                    $source[1]($row, $rowKey, $this->dim->id, ...$source[2]);
        }
        return $values;
    }

    /**
     * Add values to caluclate() or sheet() attributes
     * Note: Adding values here is faster than returning values and calling add()
     * from report class.
     * @param object $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     * @param array $total The itmes property of the total collector.
     */
    public function addValues(object $row, $rowKey, array $total): void {
        foreach ($this->calcValueSources as $name => $source) {
            $attr = $source[1];
            switch ($source[0]) {
                case Helper::ATTRIBUTE:
                    $total[$name]->add($row->$attr);
                    break;
                case Helper::SHEETATTRIBUTES:
                    // call add() with an array having one key=>value element
                    $total[$name]->add([$row->{$attr[0]} => $row->{$attr[1]}]);
                    break;
                case Helper::CLOSURE:
                    $total[$name]->add($attr($row, $rowKey, $this->dim->id, ...$source[2]));
                    break;
                case Helper::METHOD:
                    $total[$name]->add($row->$attr(...$source[2]));
                    break;
                default:
                    // called method must return an scalar value for aggregate()
                    // or an array for sheets.
                    $total[$name]->add($attr($row, $rowKey, $this->dim->id, ...$source[2]));
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
        switch ($this->dataSource[0]) {
            case Helper::ATTRIBUTE:
                return $row->{$this->dataSource[1]};
            case Helper::CLOSURE:
                return ($this->dataSource[1])($row, $rowKey, $this->dim->nextID, ... $this->dataSource[2]);
            case Helper::METHOD:
                return $row->{$this->dataSource[1]}(... $this->dataSource[2]);
            default: // CLASSMETHOD
                return ($this->dataSource[1])($row, $rowKey, $this->dim->nextID, ... $this->dataSource[2]);
        }
    }

}
