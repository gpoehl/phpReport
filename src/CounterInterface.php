<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 *
 * @author Günter
 */
interface CounterInterface {
    public function nn(int $level = null);
    public function nz(int $level = null);
}
