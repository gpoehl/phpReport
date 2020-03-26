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

    public string $groupName;          // The name of group
    public int $level;              // The group level
    public int $dimID;              // The dim this group belongs to. 

    /** @var header action having action type and action. % sign was replaced */
    public array $headerAction;

    /** @var header action having action type and action. % sign was replaced */
    public array $footerAction;

    /** @var prepared header action to be executed */
    public ?array $runtimeHeaderAction;

    /** @var prepared footer action to be executed */
    public ?array $runtimeFooterAction;

    public function __construct(string $groupName, int $level, int $dimID) {
        $this->groupName = $groupName;
        $this->level = $level;
        $this->dimID = $dimID;
    }

}
