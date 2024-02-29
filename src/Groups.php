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
class Groups
{

    /** @var Group[] Group object indexed by group level. No group for grandTotal */
    public array $items = [];

    /** @var int[] Group level indexed by group name. Includes also grandTotal name */
    public array $groupLevel = [];

    /**
     * @param $grandTotalName Name for grand total group (Level = 0)
     */
    public function __construct(public string $totalName) {
        $this->groupLevel[$totalName] = 0;
    }

    /**
     * Add a group to group items
     * @param \gpoehl\phpReport\Group $group The group object
     * @return The current group level
     * @throws InvalidArgumentException when group name exists
     */
    public function addGroup(Group $group):int {
        if (isset($this->groupLevel[$group->name])) {
            throw new \InvalidArgumentException("Group $group->name already exists.");
        }
        $group->level = count($this->groupLevel);
        $this->groupLevel[$group->name] = $group->level;
        $this->items[$group->level] = $group;
        return $group->level;
    }

}
