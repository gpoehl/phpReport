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

    /** @var  Maximum level excluding detail level. */
    public int $maxLevel = 0;

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
            throw new InvalidArgumentException("Group $group->name has already been defined");
        }
        $group->level = ++$this->maxLevel;
        $this->items[$this->maxLevel] = $group;
        $this->groupLevel[$group->name] = $this->maxLevel;
    }

}
