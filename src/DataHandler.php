<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Description of DataHandler
 *
 * @author Günter
 */
Abstract Class DataHandler {
    /* @var $dim Dimension */
    public $dim;
    /* @var $target Target class set during instantiation of phpReport class */
    public $target;
    
//    
    // Data type for data source
    public $dataType;
    // Array of defined groups. Key is group name, first element the source type, second the source.
    public $groups;
//    // 
//    public $calcs;
    public $dimID;

    const ATTRIBUTE = 1;
    const CLOSURE = 2;
    const METHOD = 3;
    const CLASSMETHOD = 4;
    const SHEETATTRIBUTES = 5;
    
     public function __construct(Dimension $dim, $target) {
        $this->dim = $dim;
        $this->target = $target;
    }
    
//    abstract function getGroupValues(mixed $row, $rowKey, Dimension $dim): array; 
//    abstract function getDimData($row, $rowKey, $target, Dimension $dim); 

}
