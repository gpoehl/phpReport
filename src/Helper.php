<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Description of Helper
 *
 * @author Günter
 */
class Helper {
 /**
     * Find the correct configuration array. When no parameter on a group is
     * set in config then the generic config parameter is used. 
     * @param $key Group $group
     * @param array $config
     * @return array The configuration for the given grouop
     */
    static function getConfigValue($key, $config) {
        if (isset($config[1])) {
            // Parameter for individual methods exists
            if (array_key_exists($key, $config[1])) {
                // When group has individual parameter take this one else take
                // the generic parameter on key = 0.
                return $config[1][$key];
            }
            $action = $config[0];
//        } else {
//            // No individual configuration is declared. Parameter is valid for
//            // all keys
//            $action = $config[0];
        }
        $action = $config[0];
        if ($action < Report::CALLABLE) {
            return $action;
        }
        // Replace % only on class,method arrary or method
        $action[1] = str_replace('%', $key, $action[1]);
        return $action;
    }

  

}
