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
    public function getGroupValues($row, $rowKey, Dimension $dim): array {
        if (is_object($row)) {
            $dim->dataHandler = new ObjectDataHandler();
        } else {
            $dim->dataHandler = new ArrayDataHandler();
        }
        return $dim->dataHandler->getGroupValues($row, $rowKey, $dim);
    }

}
