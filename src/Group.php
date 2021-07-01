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
 
    // @var The group header action to be executed before the headerAction
    public Action $beforeAction;  
    // @var The group header action to be executed on begin of a group change
    public Action $headerAction;  
    // @var The group footer action to be executed after all group members are handled.
    public Action $footerAction;
    // @var The group header action to be executed after the footerAction
    public Action $afterAction;  

    /**
     * 
     * @param string $name The group name
     * @param int $level The group level
     * @param int $dimID The id of the dim this group belongs to.
     * @param mixed $valueSource The location or callable where to find the group value from a data row.
     * Will be unset when dimension instatiates getter class
     * @param array $params Parameters passed unpacked when $valueSource is a callable.
     * Will be unset when dimension instatiates getter class
     */
    public function __construct(public string $name, public int $level, public int $dimID, public $valueSource,  public array $params) {
      if ($level < 1) {
            throw new \InvalidArgumentException("Grouplevel '$level' must not be less one");
        }
        if ($dimID < 0) {
            throw new \InvalidArgumentException("DimID '$dimID' must not be less zero");
        }
    }
}
