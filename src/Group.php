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
 * Hold some properties of a group
 */
class Group {

    public $groupName;          // The name of group
    public $level;              // The group level
    public $dimID;              // The dim this group belongs to. 
    public $headerParam;        // header parameter given by group().
    public $footerParam;        // footer parameter given by group().
    public $headerAction = [];  // Runtime header action to be performed on group change
    public $footerAction = [];  // Runtime footer action to be performed on group change

    public function __construct(string $groupName, int $level, int $dimID) {
        $this->groupName = $groupName;
        $this->level = $level;
        $this->dimID = $dimID;
    }

    /**
     * Build variable part of method name for group header or group footer.
     * @param Group The group for which the % sign might be replaced
     * @param array $configParam The configuration parameter valid for the given
     * group.
     * @return array Config param where % sign is replaced by groupName or Level
     */
    public function getGroupNameReplacement($buildMethodsByGroupName) {
        If ($buildMethodsByGroupName === 'ucfirst') {
            return ucfirst($this->groupName);
        } elseif ($buildMethodsByGroupName === true) {
            return $this->groupName;
        }
        return $this->level;
    }

}
