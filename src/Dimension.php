<?php

declare(strict_types=1);
/*
 * @author Günter Pöhl
 */

namespace gpoehl\phpReport;

/**
 * For every group an instance of group is created.
 * Group class just hold a few properties which could also be stored as an array.
 *
 * 
 */
class Dimension {

    public $fromLevel;
    public $lastLevel;
    public $data;            // Attribute name in row holding next dim data or
    // Closure which return next dim data
    public $method;          // Method which handles next dim data. This method
    // must call run() or next() methods.
    public $groupAttr = [];   //Attributes for groups. Key is group name, value
    // the key or attribute in row. 
    public $sumClosures = [];
    public $addCmd;

    /**
     * Holds closure to call addValues() in main class or the dynamicly build content.
     * So there is no need to check at every row if it's the first one for
     * this dimension.
     */
    public $addClosure;
    
    
    public $row;               // Array Current row and rowKey
    public $groupValues =[];   // Array of group values to detect group change
    
    public function __construct($data = null, $method = null) {
        $this->data = $data;
        $this->method = $method;
    }

    public function addGroup($group) {
        $this->groups[] = $group;
    }

}
