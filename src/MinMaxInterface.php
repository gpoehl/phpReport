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
interface MinMaxInterface {
    public function min(int $level = null);
    public function max(int $level = null);
}
