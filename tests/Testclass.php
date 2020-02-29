<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Testclass
 *
 * @author Günter
 */
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
