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
 * Groups instantiates and hold group objects.
 * Access to group objects can be by name or level.
 */
class Groups {

    public $items = [];                 // Array Key is group level, value is group object.
    public $groupLevel = [];            // Array Key is groupName, value is group level.
    public $values = [0 => null];       // Active group values. Key is level. 
    public $maxLevel = 0;               // Maximum level excluding detail level.

    /**
     * 
     * @param string $grandTotalName Name for grand total group (Level = 0)
     */
    public function __construct(string $grandTotalName) {
        $this->groupLevel[$grandTotalName] = 0;
    }

    /**
     * Add new group. 
     * Instantiate a new group object and store the reference into array $itmes.
     * @param string $groupName The name of the group
     * @param int $dim The dimension the group belongs to
     * @return Group The new group object
     * @throws InvalidArgumentException when group has already been defined.
     */
    public function newGroup(string $groupName, int $dim): Group {
        if (isset($this->groupLevel[$groupName])) {
            throw new InvalidArgumentException("Group $groupName has already been defined");
        }
        $this->maxLevel ++;
        $group = new Group(
                $groupName
                , $this->maxLevel
                , $dim
        );
        $this->items[$this->maxLevel] = $group;
        $this->groupLevel[$groupName] = $this->maxLevel;
        return $group;
    }

}
