<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2021 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */
declare(strict_types=1);

namespace gpoehl\phpReport;

/**
 * Configurator handles configuration options
 * It loads the config file and merges configuration parameters
 */
class Configurator
{

    /**
     * @var $action[] Default actions indexed by the action key.
     * Action keys are mapped to actions to be invoked.
     * Action is usually a method to be called within the owner class or 
     * a string to be appended to $output. But there are much more options.
     * Please check the documentation.
     * 
     * Percent sign (%) will be replaced
     * - in beforeGroup, groupHeader, groupFooter and afterGroup by a pattern
     * depending on $buildMethodsByGroupName rules.
     * - in totalHeader and totalFooter by the value of $grandTotalName.
     * - in noData_n, detail_n and nogroupChange_n by the dimension ID.
     */
    public array $actions = [
        'init' => 'init',
        'totalHeader' => '%Header',
        'beforeGroup' => '%Before',
        'groupHeader' => '%Header',
        'detailHeader' => 'detailHeader',
        'detail' => 'detail',
        'detailFooter' => 'detailFooter',
        'groupFooter' => '%Footer',
        'afterGroup' => '%After',
        'totalFooter' => '%Footer',
        'close' => 'close',
        'noData' => '<br><strong>No data found</strong><br>', // Dimension = 0
        'noData_n' => 'noDataDim%',
        'detail_n' => 'detail%',
        'noGroupChange_n' => ["Current row in dimension % didn't trigger a group change.", Action::ERROR],
    ];

    /** @var Classname for default output handler */
    public string $outputHandler = output\StringOutput::class;

    /** @var true | false | 'ucfirst' Rule to build method groupheader and -footer 
     * as well as totalHeader and -footer names. 
     * True will replace the % sign by the group name, false by the groupID. 
     * 'ucfirst' will use the reselut of the ucfirst($groupName) function. 
     */
    public $buildMethodsByGroupName = true;

    /** @var The name of the grand total group (Level = 0). */
    public string $grandTotalName = 'total';

    /** @var Name and location of the configuration file. */
    static $filename = '/../config.php';

    /**
     * Read configuration file and handle $config parameter.
     */
    public function __construct(array $config = null) {
        $this->loadConfigurationFile();
        $this->setConfiguration($config);
    }

    /**
     * Load configuration from configuration file.
     * Parameters from config file replaces default values.
     * @throws Exception When file can't be loaded.
     */
    private function loadConfigurationFile(): void {
        if (!is_readable(__DIR__ . self::$filename)) {
            throw new \Exception("Unable to load configuration file " . self::$filename . ".");
        }
        $configFile = require __DIR__ . self::$filename;
        $this->setConfiguration($configFile);
    }

    /**
     * Merge config into configuration
     * @param array|null $config array holding configuration parameter from 
     * config file or constructor parameter. 
     * @throws InvalidArgumentException
     */
    private function setConfiguration(array $config = null): void {
        if ($config !== null) {
            foreach ($config as $param => $value) {
                $result = match ($param) {
                    'buildMethodsByGroupName' => $this->setBuildMethodsByGroupName($value),
                    'grandTotalName' => $this->setGrandTotalName($value),
                    'actions' => $this->setActions($value),
                    default => throw new \InvalidArgumentException("Unknown configuration parameter $param."),
                };
            }
        }
    }

    /**
     * Set $value for parameter $setBuildMethodsByGroupName.
     * Check that $value is valid. 
     * Only true, false or 'ucfirst' is allowed
     * @param bool|string $value Value of $setBuildMethodsByGroupName parameter.
     * Allowed is true, false or 'ucfirst'
     * @throws InvalidArgumentException
     */
    private function setBuildMethodsByGroupName($value): void {
        if (!is_bool($value)) {
            $value = trim(strtolower($value));
            if ($value !== 'ucfirst') {
                throw new \InvalidArgumentException("BuildMethodsByGroupName must be true, false or 'ucfirst'");
            }
        }
        $this->buildMethodsByGroupName = $value;
    }

    /**
     * Set the name for the grand total group
     * @param string $name The name of the grand total group (level = 0)
     * @throws InvalidArgumentException
     */
    private function setGrandTotalName(string $name): void {
        $name = trim($name);
        if ($name === null || $name == '') {
            throw new \InvalidArgumentException("Grand total name can not be empty.");
        }
        if (!Action::isNameValid($name)) {
            throw new \InvalidArgumentException("Grand total name '$name' is invalid.");
        }

        $this->grandTotalName = $name;
    }

    /**
     * Replace given action values. 
     * Action key must already exist. Action type will be derived from action.  
     * @param array $actions 
     * @throws InvalidArgumentException
     */
    private function setActions(array $actions): void {
        foreach ($actions as $key => $baseAction) {
            // Make sure method key is valid
            if (!isset($this->actions[$key])) {
                throw new \InvalidArgumentException("Action key '$key' is invalid");
            }
            $this->actions[$key] = $baseAction;
        }
    }

}
