<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Description of NullExecutor
 *
 * @author Günter
 */
class SelectExecutor {

    public static function getExecuter(bool $execute, int $actionType) {
        if (!$execute) {
            return new NullExecutor();
        } else {
            switch ($actionType) {
                case Report::STRING:
                    return new StringExecutor();
                case Report::CLOSURE:
                case Report::CALLABLE:
                case Report::METHOD:
                    return new MethodExecutor();
                case Report::WARNING:
                    return new WarningExecutor();
                case Report::ERROR:
                    return new ErrorExecutor();
                default:
                    throw new \InvalidArgumentException("Action type $actionType is invalid");
            }
        }
    }

}
