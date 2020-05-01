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
class MethodExecutor extends Executor {

    public function execute(...$params) {
        return ($this->runTimeAction)(...$params);
    }
    
}
