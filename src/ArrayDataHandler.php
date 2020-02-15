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
 * Methods related to data being arrays
 */
class ArrayDataHandler {

    /**
     * Get group values out of the current row
     * @param array $row The row which has the group attributes.
     * @param int | string | null $rowKey The key of $row. 
     * @param Dimension Object which serves $groupAttr.
     * @return array Indexed array having the group values out of the given row.
     */
    public function getGroupValues(array $row, $rowKey, Dimension $dim): array {
        $values = [];
        foreach ($dim->groupAttr as $groupAttr) {
            $values[] = ($groupAttr instanceof \Closure) ? $groupAttr($row, $rowKey) : $row[$groupAttr];
        }
        return $values;
    }

    /**
     * Add values to caluclate() or sheet() attributes
     * @param array $row The current row.
     * @param int | string | null $rowKey The key of $row. 
     * @param array $total The itmes property of the total collector.
     * @param Dimension Object which serves $attrSource and $attrType.
     */
    public function addValues(array $row, $rowKey, array $total, Dimension $dim): void {
        foreach ($dim->attrSource as $name => $attr) {
            switch ($dim->attrType[$name]) {
                case 1:         // Single Variable
                    $total[$name]->add($row[$attr]);
                    break;
                case 2:         // Closure
                    $total[$name]->add($attr($row, $rowKey));
                    break;
                case 3:         // Array (key <-> value pair for sheets)
                    $total[$name]->add([$row[key($attr)] => $row[current($attr)]]);
                    break;
            }
        }
    }

}
