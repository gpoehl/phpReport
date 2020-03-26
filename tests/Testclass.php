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
class Testclass {

    public static function getStaticValueFromArray($row, $rowKey, $dimID, array $params) {
        return $row[$params['attr']];
    }

    public function getValueFromArray($row, $rowKey, $dimID, array $params) {
        return $row[$params['attr']];
    }

    public static function getStaticValueFromObject($row, $rowKey, $dimID, $attr, $mult = null) {
        
         return ($mult === null) ? $row->$attr : $row->$attr * $mult;
    }

    public function getValueFromObject($row, $rowKey, $dimID, $attr, $mult = null) {
          return ($mult === null) ? $row->$attr : $row->$attr * $mult;
    }

}
