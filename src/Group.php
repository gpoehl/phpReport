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

namespace gpoehl\phpReport;

/**
 * Group class to handle group changes
 */
class Group {

    public string $name;            // The name of group
    public int $level;              // The group level
    public int $dimID;              // The dim this group belongs to. 

    /** @var mixed The location or callable where to find the group value from a data row.
     * Will be unset when dimension instatiates getter class
     */
    public $valueSource;

    /** @var array|empty Optional variadic array of parameters to get group value out of a data row. 
     * Will be unset when dimension instatiates getter class
     */
    public $params;
    
    public Action $headerAction;
    public Action $footerAction;

    public function __construct(string $name, int $dimID, $valueSource, $params) {
        $this->name = $name;
        $this->dimID = $dimID;
        $this->valueSource = $valueSource;
        $this->params = $params;
    }

}
