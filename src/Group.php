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

    /* @var $actions Action objects indexed by ActionKey enum. */
    public \WeakMap $actions;
            
   
     // @var The group level set when group is added to Groups
    public int $level;

    /**
     * 
     * @param string $name The group name
     * @param $dimID The id of the dim this group belongs to.
     * @param mixed $valueSource The location or callable where to find the group value from a data row.
     * Will be unset when dimension instatiates getter class
     * @param array $params Parameters passed unpacked when $valueSource is a callable.
     * Will be unset when dimension instatiates getter class
     */
    public function __construct(public readonly string $name, public readonly int $dimID,  public $valueSource, public array $params) {
      $this->actions = new \WeakMap;
        if ($dimID < 0) {
            throw new \InvalidArgumentException("DimID '$dimID' must not be less zero");
        }
    }

}
