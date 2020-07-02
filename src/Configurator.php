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
 * Configurator handles configuration options
 * It loads the config file and merges configuration parameters
 */
class Configurator {
    
    /**
     * @var array[] An array of all possible actions.
     * Action names are mapped to actions to be exected.
     * Key is the action name while value is an array having the the action
     * type at first element and the action to be executed as second element.
     * Action is usually a method to be called within the owner class or 
     * a string to be appended to $output. But there are much more options.
     * Please check the documentation.
     * 
     * The percent sign (%) in groupHeader and groupFooter will be replaced by a 
     * pattern build depending on $buildMethodsByGroupName rules.
     * The percent sign in totalHeader and totalFooter actions will be replaced 
     * by the value of $grandTotalName.
     * In data_n and noData_n actions the percent sign will be replaced by the
     * current dimension id.
     */
    public $actions = [
        'init' => [Action::METHOD, 'init'],
        'totalHeader' => [Action::METHOD, '%Header'],
        'groupHeader' => [Action::METHOD, '%Header'],
        'detail' => [Action::METHOD, 'detail'],
        'groupFooter' => [Action::METHOD, '%Footer'],
        'totalFooter' => [Action::METHOD, '%Footer'],
        'close' => [Action::METHOD, 'close'],
        // : sign declares string explicit to avoid method calls when callOption = CALL_ALWAYS
        'noData' => [Action::STRING, '<br><strong>No data found</strong><br>'], // Dimension = 0
        'noData_n' => [Action::METHOD, 'noDataDim%'],
        'detail_n' => [Action::METHOD, false],
        'noGroupChange_n' => [Action::ERROR, "error:Current row in dimension % didn't trigger a group change."],
    ];
    
    /** @var true | false | 'ucfirst' Rule to build method groupheader and -footer names. */ 
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
        $this->finalize();
    }

    /**
     * Load configuration from configuration file.
     * Parameters from config file will replace default values.
     * @throws Exception When file can not be loaded.
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
        if (is_null($config)) {
            return;
        }
        foreach ($config as $param => $value) {
            switch ($param) {
                case 'buildMethodsByGroupName':
                    $this->setBuildMethodsByGroupName($value);
                    break;
                case 'grandTotalName':
                    $this->setGrandTotalName($value);
                    break;
                case 'actions':
                    $this->setActions($value);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown configuration parameter $param.");
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
        if ($value !== true && $value !== false) {
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
            return;
        }
        if (!Helper::isValidName($name)) {
            throw new \InvalidArgumentException("grandTotalName must be a valid attribute name ('$name' given). ");
        }
        $this->grandTotalName = $name;
    }

    /**
     * Replace given action values. 
     * Action key must already exist. Action type will be derived from action.  
     * @param array $actions 
     * @throws InvalidArgumentException
     */
    private function setActions(array $actions) :void {
        foreach ($actions as $key => $baseAction) {
            // Make sure method key is valid
            if (!isset($this->actions[$key])) {
                throw new \InvalidArgumentException("Action key '$key' is invalid");
            }
            $this->actions[$key] = Helper::buildMethodAction(
                            $baseAction,
                            $key,
                            !in_array($key, ['init', 'detail', 'close', 'noData'])
            );
        }
    }

    /**
     * Replace % sign in totalHeader and totalFooter actions with GrandTotalName
     */
    private function finalize() :void {
        foreach (['totalHeader', 'totalFooter'] as $key) {
            $this->actions[$key] = Helper::replacePercent($this->grandTotalName, $this->actions[$key]);
        }
    }

}
