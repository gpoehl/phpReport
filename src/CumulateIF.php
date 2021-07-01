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
interface CumulateIF
{
    public function cumulateToNextLevel(int $level): void;
}
