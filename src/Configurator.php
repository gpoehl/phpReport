<?php

declare(strict_types=1);

namespace gpoehl\phpReport;

use Closure;
use InvalidArgumentException;
use Exception;

/**
 * Configurator handles configuration options
 * Load config file and merge with configuration parameter
 *
 * @author Guenter
 */
class Configurator {

    /**
     * @var array $methods Action to be executed on $key events.
     * Key is the action name while value is the action to be executed.
     * Action can be a method name to be called (defaults within the owner class),
     * a closure or a string to be appended to $output.
     * 
     * Percent signs in groupHeader and groupFooter will be replaced by a 
     * pattern build depending on $buildMethodsByGroupName rules.
     * Percent sign in fetchValues_n will be replaced by the dimension
     */
    public $methods = [
        'init' => 'init',
        'totalHeader' => [false, 'header'],
        'groupHeader' => [false, 'header%'],
        'detail' => [false, 'detail'],
        'groupFooter' => ['footer%'],
        'totalFooter' => ['footer'],
        'close' => [false, 'close'],
        'fetchValues' => [false, 'fetchValues'], // Dimension = 0. 
        'fetchValues_n' => [false, 'fetchValues%'], // Dimension > 0. 
        // : sign declares string explicid to avoid method calls when callOption = CALL_ALWAYS
        'noData' => [true, '<br><strong>No data found</strong><br>'], // Dimension = 0
        'noData_n' => [false, 'noData%'],
    ];
    public $buildMethodsByGroupName = 'ucfirst';
    public $userConfig;
    static $filename = '/../config.php';
    // pattern has been taken from php documentation
    // pattern_n is like pattern but accepts also the % sign
    private $pattern = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/";
    private $pattern_n = "/^[a-zA-Z_%\x7f-\xff][a-zA-Z0-9_%\x7f-\xff]*$/";

    public function __construct(array $config = null) {
        $this->loadConfigurationFile();
        $this->setConfiguration($config);
    }

    /**
     * Load configuration from configuration file
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
     * When a config parameter is a real array (is_array plus element[1] is_array)
     * then the array data will be use at all. When first element equals true
     * the value from config file will not be overwritten. 
     * @param array|null $config Dynamic configuration option to replace
     * defaults of config.php file.
     * @throws InvalidArgumentException
     */
    private function setConfiguration(array $config = null): void {
        if (is_null($config)) {
            return;
        }
        // merge config. Single values are overwritten while arrays are merged.
        foreach ($config as $param => $value) {
            switch ($param) {
                case 'buildMethodsByGroupName':
                    $this->setBuildMethodsByGroupName($value);
                    break;
                case 'userConfig':
                    $this->$param = $value;
                    break;
                case 'methods':
                    $this->setMethods($value);
                    break;
                default:
                    throw new InvalidArgumentException("Unknown configuration parameter $param.");
            }
        }
    }

    private function setBuildMethodsByGroupName($value) {

        if ($value !== true && $value !== false) {
            $value = strtolower($value);
            if ($value !== 'ucfirst') {
                throw new InvalidArgumentException("BuildMethodsByGroupName must be false, true or 'ucfirst'");
            }
        }
        $this->buildMethodsByGroupName = $value;
    }

    private function setMethods($methods) {
        // Parameter itself is also an array (key = methods)
        foreach ($methods as $key => $method) {
            if (!array_key_exists($key, $this->methods)) {
                throw new InvalidArgumentException("Method key '$key' is invalid");
            }

            if ($method instanceOf Closure) {
                $this->methods[$key] = [Backbone::CLOSURE, $method];
                continue;
            }
            if (!in_array($key, ['groupHeader', 'groupFooter', 'fetchValues_n', 'noData_n'])) {
                $this->methods[$key] = (is_array($method)) ? $this->validateCallable($method, $key) : $this->stringOrMethod($method);
                continue;
            }
            // Parameters for methods xxx_n  
            if (!is_array($method)) {
                // only string or method name. 
                $this->methods[$key] = [$this->stringOrMethod($method, true), null];
                continue;
            }
            // 
            $this->validateCallable($method, $key);  // must be an array with 2 elements
            if (is_array($method[1])) {
                // Parameter for level or dimension is given
                $this->methodArray($key, $method);
            } else {
                $this->methods[$key] = [Backbone::CALLABLE, $method];
            }
        }
    }

    /**
     * Handle individual parameters for methods_n  
     * @param string $key the method key
     * @param array $method The config method parameter for methods_n 
     */
    private function methodArray($key, array $method) {
        If ($method[0] !== true) {
            // Don't keep value of first element. (Default value for all occurreces]
            $this->methods[$key][0] = detectCallType($method[0]);
        }
        // Parameter itself is also an array (key = methods)
        foreach ($method[1] as $key_n => $method_n) {
            $this->methods[$key][1][$key_n] = $this->detectCallType($method_n, $key);
        }
    }

    private function detectCallType($method, bool $allowPercentSign = false) {
        if ($method instanceOf Closure) {
            return [Report::CLOSURE, $method];
        }
        return (is_array($method)) ? $this->validateCallable($method, $key) : $this->stringOrMethod($method, $allowPercentSign);
    }

    /**
     * Validate that a method is a valid callable.
     * A callable is valid when $method is an array having 2 elements.  
     * @param type $method
     * @param string $key The method key. Just to throw meaningful exception.
     * @return array When validation is successfull return an array where
     * the first element has the static CALLABLE and the second element the given $method.
     * @throws InvalidArgumentException
     */
    private function validateCallable($method, string $key) {
        if (count($method) !== 2) {
            throw new InvalidArgumentException("Only single parameter or array with 2 elements allowed for $key");
        }
        return [Report::CALLABLE, $method];
    }

    /**
     * Check if the given value should be handled as a method name or as a string 
     * Result of this check is returned in the first array element.
     * A colon (:) at beginning of value forces the value to be a normal string.
     * In this case the colon is truncated.
     * @param string $value The string to be checked
     * @return array First element is a boolean indicating if $value is a
     * string while second value has the value without trailing colon.
     */
    private function stringOrMethod(string $value, bool $allowPercentSign = false): array {
        if (substr($value, 0, 1) === ':') {
            return [Report::STRING, substr($value, 1)];
        }
        $isValidMethodName = preg_match(($allowPercentSign) ? $this->pattern_n : $this->pattern, $value);
        return ($isValidMethodName) ? [Report::METHOD, $value] : [Report::STRING, $value];
    }

}
