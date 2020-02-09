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
use Exception;

/**
 * Configurator handles configuration options
 * Load config file and merge with configuration parameter
 */
class Configurator {
    
    /**
     * @var array $actions Action to be executed on key events.
     * Key is the action name while value is an array having the the action
     * type at first element and the action to be executed as second element.
     * Action can be a method name to be called (defaults within the owner class),
     * a closure or a string to be appended to $output.
     * 
     * Percent sign (%) in groupHeader and groupFooter will be replaced by a 
     * pattern build depending on $buildMethodsByGroupName rules.
     * Percent sign in totalHeader and totalFooter will be replaced by the value
     * of $grandTotalName.
     * Percent sign in data_n and noData_n will be replaced by number of dimension.
     */
    public $actions = [
        'init' => [Report::METHOD, 'init'],
        'totalHeader' => [Report::METHOD, '%Header'],
        'groupHeader' => [Report::METHOD, '%Header'],
        'detail' => [Report::METHOD, 'detail'],
        'groupFooter' => [Report::METHOD, '%Footer'],
        'totalFooter' => [Report::METHOD, '%Footer'],
        'close' => [Report::METHOD, 'close'],
        // : sign declares string explicit to avoid method calls when callOption = CALL_ALWAYS
        'noData' => [Report::STRING, '<br><strong>No data found</strong><br>'], // Dimension = 0
        'noData_n' => [Report::METHOD, 'noDataDim%'],
        'data_n' => [Report::METHOD, false],
        'noGroupChange_n' => [Report::ERROR, "error:Current row in dimension % didn't trigger a group change."],
    ];
    // $buildMethodsByGroupName can be true, false or 'ucfirst'
    public $buildMethodsByGroupName = true;

    /**
     * For dimensions having data for the next dimension only.
     * Action to be executed when group is defined but row doesn't trigger a group
     * change. This should happen only when data is not well normalized or might
     * happen when group attributes are not set properly.
     * To avoid this situation you might use the distinct option
     * while running an SQL select statement or join data via a left join. 
     * You might also define a dummy group attribute.
     * To trigger a warning precede a text message with 'warning:'. To throw a
     * runTimeException precede a text message with 'error:'.
     */
    // Name of the grand total group (Level = 0)
    public $grandTotalName = 'total';
    // optional parameters
    public $userConfig;
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
     * @throws Exception
     */
    private function loadConfigurationFile(): void {
        if (!is_readable(__DIR__ . self::$filename)) {
            throw new Exception("Unable to load configuration file " . self::$filename . ".");
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
                case 'userConfig':
                    $this->userConfig = $value;
                    break;
                case 'actions':
                    $this->setActions($value);
                    break;
                default:
                    throw new InvalidArgumentException("Unknown configuration parameter $param.");
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
                throw new InvalidArgumentException("BuildMethodsByGroupName must be true, false or 'ucfirst'");
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
            throw new InvalidArgumentException("grandTotalName must be a valid attribute name ('$name' given). ");
        }
        $this->grandTotalName = $name;
    }

    /**
     * Replace given action values. 
     * Action key must already exist. Action type will be derived from action.  
     * @param array $actions 
     * @throws InvalidArgumentException
     */
    private function setActions($actions) {
        foreach ($actions as $key => $baseAction) {
            // Make sure method key is valid
            if (!isset($this->actions[$key])) {
                throw new InvalidArgumentException("Action key '$key' is invalid");
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
    private function finalize() {
        foreach (['totalHeader', 'totalFooter'] as $key) {
            $this->actions[$key] = Helper::replacePercent($this->grandTotalName, $this->actions[$key]);
        }
    }

}
