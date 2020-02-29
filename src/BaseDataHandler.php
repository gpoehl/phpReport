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
 * BaseDataHandler provides methods for data handlers which are independent
 * of the data type of data row.
 */
trait BaseDataHandler {
    /* @var $dim Dimension */

    private $dim;
    // Next data dim source. Array having source type, the data source and at key 2 the params.
    private $dataSource;
    // Array of defined groups. Key is group name, first element the source type, second the source,third are params.
    private $groupValueSources = [];
    // Array of defined calcs or sheets. Key is name, first element the source type, second the source, third are params.
    private $calcValueSources = [];
    public $numberOfGroups = 0;
    
    
/**
 * 
 * @param \gpoehl\phpReport\Dimension $dim inversion to the dimension class
 * @param mixed $source data source for next dimension
 * @param mixed $params Variadic list of parameters passed to callables
 */
    public function __construct(\gpoehl\phpReport\Dimension $dim, $source, $params) {
        $this->dim = $dim;
        $this->dataSource = Helper::getSourceType($source);
        $this->dataSource[] = $params;
    }

    /**
     * Set the inversion to the dimension class
     * @param \gpoehl\phpReport\Dimension $dim
     */
    public function setDim(Dimension $dim): void {
        
    }

    /**
     * Set the source for the next data dimension
     * @param mixed $source
     * @param mixed $params Variadic list of parameters passed to callables 
     */
    public function setNextDataDimSource($source, $params): void {
        
    }

    /**
     * Set the source for group values
     * @param mixed $source
     * @param mixed $params Variadic list of parameters passed to callables 
     */
    public function addGroup($source, $params): void {
        $result = Helper::getSourceType($source);
        if ($result[0] > Helper::CLOSURE) {
            throw new \InvalidArgumentException("Callables are not valid for group values.");
        }
        $result[] = $params;
        $this->groupValueSources[] = $result;
        $this->numberOfGroups++;
    }

    /**
     * Set the source for calc values
     * @param mixed $name The name of calc item
     * @param mixed $source
     * @param mixed $params Variadic list of parameters passed to callables 
     */
    public function addCalcItem($name, $source, $params) {
        $this->calcValueSources[$name] = Helper::getSourceType($source);
        $this->calcValueSources[$name][] = $params;
    }

    /**
     * Set the source for sheet keys and values
     * @param mixed $name The name of sheet item
     * @param mixed $source
     * @param mixed $params Variadic list of parameters passed to callables 
     */
    public function addSheetItem($name, $source, $params) {
        $this->calcValueSources[$name] = Helper::getSheetSourceType($source);
        $this->calcValueSources[$name][] = $params;
    }

    abstract public function getGroupValues($row, $rowKey): array;

    abstract public function addValues($row, $rowKey, arrray $total): void;

    abstract public function getDimData($row, $rowKey);
}
