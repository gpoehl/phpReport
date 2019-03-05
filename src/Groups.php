<?php

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Groups instantiates and hold group objects.
 * Access to group objects can be by name or level.
 *
 * @author Guenter
 */
class Groups {

    private $buildMethodsByGroupName;
    public $groups = [];         // Access group object via numeric level. Index starts with 1.
    public $groupsByName = [];   // Access group object via group name.
    public $groupLevel = [];     // Holds only the group level by groupname. Allows fast access 
    public $values = [0 => null];  // Active group values. Key is level. 
    public $maxLevel = 0;          // Maximum level excluding detail level.

    /**
     * @param mixed $buildMethodsByGroupname False, True or 'ucfirst'
     */
    public function __construct( $buildMethodsByGroupname = true) {
        $this->buildMethodsByGroupName = $buildMethodsByGroupname;
    }

    /**
     * Add new group. 
     * Instantiate a new group object and store the reference into arrays $groups
     * and $groupsX. The first has an numeric key while the later stores them by name.
     * @param string $groupName The name of the group
     * @param int $dim The dimension the group belongs to
     * @return Group The new group object
     * @throws Exception
     */
    public function newGroup(string $groupName, int $dim) {
        if (array_key_exists($groupName, $this->groupsByName)) {
            throw new \Exception("Group $groupName has already been defined");
        }
        $this->maxLevel ++;
        $group = new Group(
                $groupName
                , $this->maxLevel
                , $dim
        );
        $this->groups[$this->maxLevel] = $group;
        $this->groupsByName[$groupName] = $group;
        $this->groupLevel[$groupName] = $this->maxLevel;
        return $group;
    }

    /**
     * Keep group values by level
     * @param int $fromLevel The starting level of $values
     * @param array $values
     */
    public function setValues(int $fromLevel, array $values):void {
        array_splice($this->values, $fromLevel, count($this->values), $values); 
    }
    
    /**
     * Build method name for group header or group footer
     * @param Group The group for which the % sign might be replaced
     * @param array $configParam The configuration parameter valid for the given
     * group.
     * @return array Config param where % sign is replaced by groupName or Level
     */
    public function getMethodNameReplacement(Group $group) {
        switch ($this->buildMethodsByGroupName) {
            case false:
                return $group->level;
            case 'ucfirst':
                return ucfirst($group->groupName);
            default:
                return $group->groupName;
        }
    }

}
