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
class ErrorExecutor extends Executor {

    public function execute(...$params) {
         throw new \RuntimeException($this->runTimeAction . ' RowKey = ' .$params[1]);
    }
    
}
