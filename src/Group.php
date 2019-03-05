<?php

declare(strict_types=1);
/*
 * @author Günter Pöhl
 */

namespace gpoehl\phpReport;

/**
 * Hold some properties of a group
 */
class Group {

    public $groupName;          // The name of group
    public $level;              // The group level
    public $dim;                // The dim this group belongs to. 
    public $headerAction;       // Header action to be performed on group change
    public $footerAction;       // Footer action to be performed on group change

    public function __construct(string $groupName, int $level, int $dim) {
        $this->groupName = $groupName;
        $this->level = $level;
        $this->dim = $dim;
    }

}
