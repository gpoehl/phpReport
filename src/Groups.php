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

use InvalidArgumentException;

/**
 * Collector class for groups.
 */
class Groups {

    /** @var Group[] Group object indexed by group level. */
    public array $items = [];

    /** @var int[] Group level indexed by group name. */
    public array $groupLevel = [];

    /**
     * @param string $grandTotalName Name for grand total group (Level = 0)
     */
    public function __construct(string $grandTotalName) {
        $this->groupLevel[$grandTotalName] = 0;
    }

    /**
     * Add a group to group items
     * @param \gpoehl\phpReport\Group $group The group object
     * @throws InvalidArgumentException
     */
    public function addGroup(Group $group): void {
        if (isset($this->groupLevel[$group->name])) {
            throw new \InvalidArgumentException("Group $group->name has already been defined");
        }
        
        // Check group level just checks that report class did it well. 
        if (empty($this->items) && $group->level !== 1) {  
            throw new \InvalidArgumentException("First grouplevel must be 1. '$group->level' given"); 
        } 
        if (!empty($this->items) &&  $group->level <> end($this->items)->level + 1 ){
            throw new \InvalidArgumentException("Grouplevel '$group->level' must be 1 greater than the previous level"); 
        } 
        
        $this->items[$group->level] = $group;
        $this->groupLevel[$group->name] = $group->level;
    }

}
