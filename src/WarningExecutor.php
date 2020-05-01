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
class WarningExecutor extends Executor {

    public function execute(...$params) {
         trigger_error($this->runTimeAction . ' RowKey = ' . $params[1], E_USER_NOTICE);
    }
    
}
