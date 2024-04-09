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

use gpoehl\phpReport\Actionkey;

/**
 * Configurator handles configuration options
 * It loads the config file and merges configuration parameters
 */
final class Configurator {

    /**
     * @var $actions[] Default actions indexed by the action key.
     * Action keys are mapped to actions to be invoked.
     * Action is usually a method to be called within the owner class or 
     * a string to be appended to $output. 
     * 
     * Placeholders will be replaced by
     * %n - The id of group or dimension
     * %s - The name of group or dimension
     * %S - Ucfirst of the group or dimension name 
     */
    public \SplObjectStorage $actions;
    private \SplObjectStorage $defaultActions;
    private \SplObjectStorage $numberedActions;

    /** @var useNumberedActions Use numberedActions pattern. */
    private bool $useNumberedActions = false;

    /** @var The name of the grand total group (Group level = 0). */
    public string $totalName = 'total';

    /** @var The name of the detail group (lowest group level). */
    public string $detailName = 'detail';
    
     /** @var The name of the root data dimension. */
    public string $dimensionName = 'root';

    /** @var Classname for default output handler */
    public string $outputHandler = output\StringOutput::class;

    /** @var Classname for default prototye class */
    public string $prototype = Prototype::class;

    /**
     * Load configurdation.
     * Read configuration file and handle $config parameter.
     * @param config Configuration parameter indexed by parameter name.
     * @param configFilename Name of config file. 
     * ConfigFilename in $config takes precedence over this name.
     */
    public function __construct(null|iterable $config = [], string $configFilename = '') {
        $config = $config ?? [];
        $this->loadDefaultActions();
        $this->loadConfigurationFromFile($config, $configFilename);
        $this->setConfiguration($config);
        if ($this->useNumberedActions) {
            $this->defaultActions->addAll($this->numberedActions);
        }
        $this->defaultActions->addAll($this->actions);
        $this->actions = $this->defaultActions;
        unset($this->numberedActions, $this->defaultActions);
    }

    /**
     * Load configuration from configuration file.
     * Parameters from config file replaces default values.
     * When configFile Parameter is set then the file must exist.
     * When default config file is not in src directory nothing will be loaded
     * Config file in vendor directory is just an example. Copy this file to an
     * protected directory and alter entries.
     * @throws Exception When file can't be loaded.
     */
    private function loadConfigurationFromFile($config, string $configFilename): void {
        $fileName = $config['configFilename'] ?? $configFilename;
        // Don't load config file when filename is not set.
        if ($fileName == false) {
            return;
        }
        if (!is_readable($fileName)) {
            throw new \Exception("Unable to load configuration file '$fileName'.");
        }
        $this->setConfiguration(require $fileName);
    }

    /**
     * Merge config into configuration
     * @param array|null $config array holding configuration parameter from 
     * config file or constructor parameter. 
     * @throws InvalidArgumentException
     */
    private function setConfiguration(array $config = []): void {
        foreach ($config as $param => $value) {
            match ($param) {
                'totalName' => $this->totalName = $this->validateName($value, 'Grand total'),
                'detailName' => $this->detailName = $this->validateName($value, 'Detail'),
                'dimensionName' => $this->dimensionName = $this->validateName($value, 'Dimension'),
                'useNumberedActions' => $this->useNumberedActions = $value,
                'actions' => $this->replaceActions($value, $this->actions),
                'defaultActions' => $this->replaceActions($value, $this->defaultActions),
                'numberedActions' => $this->replaceActions($value, $this->numberedActions),
                'outputHandler' => $this->outputHandler = $value,
                'prototype' => $this->prototype = $value,
                'configFilename' => null, // has already taken into account
                default => throw new \InvalidArgumentException("Unknown configuration parameter $param."),
            };
        }
    }

    /**
     * Validate variable name 
     * @param string $name The name to be validated
     * @param string The message prefix in case of invalid name  
     * @throws InvalidArgumentException
     */
    private function validateName(string $name, string $messagePrefix): string {
        $name = trim($name);
        if ($name === null || $name == '') {
            throw new \InvalidArgumentException("$messagePrefix name can not be empty.");
        }
        if (!Action::isNameValid($name)) {
            throw new \InvalidArgumentException("$messagePrefix name '$name' is invalid.");
        }
        return $name;
    }

    /**
     * Replace default actions.
     * @param $actions key is an Actionkey or a string reprensenting the value of an action key.
     * Value is the action.   
     * @param $map Reference to the actions or numberedActions map
     */
    private function replaceActions(?iterable $actions, $map): void {
        foreach ($actions as $key => $action) {
            if (is_string($key)) {
                $map[Actionkey::fromName($key)] = $action;
            } else {
                $map[$key] = $action;
            }
        }
    }

    /**
     * Set default action patterns
     */
    private function loadDefaultActions() {
        $this->actions = new \SplObjectStorage;
        $ac = new \SplObjectStorage();
        $ac[ActionKey::Start] = 'start';
        $ac[ActionKey::Finish] = 'finish';
        $ac[ActionKey::TotalHeader] = 'header%S';
        $ac[ActionKey::TotalFooter] = 'footer%S';
        $ac[ActionKey::NoData] = '<br><strong>No data found</strong><br>'; // Dimension = 0
        $ac[ActionKey::DetailHeader] = 'header%S';
        $ac[ActionKey::Detail] = $this->detailName;
        $ac[ActionKey::DetailFooter] = 'footer%S';
        $ac[ActionKey::GroupBefore] = 'before%S';
        $ac[ActionKey::GroupFirst] = 'first%S';
        $ac[ActionKey::GroupHeader] = 'header%S';
        $ac[ActionKey::GroupFooter] = 'footer%S';
        $ac[ActionKey::GroupLast] = 'last%S';
        $ac[ActionKey::GroupAfter] = 'after%S';
        $ac[ActionKey::DimNoData] = 'noData%S';
        $ac[ActionKey::DimDetail] = $this->detailName . '%S';
        $ac[ActionKey::DimNoGroupChange] = ["Current row in dimension '%s' didn't trigger a group change.", Action::ERROR];
        $this->defaultActions = $ac;
        $this->loadNumberedActions();
    }

    /**
     * Set default numbered action patterns
     */
    private function loadNumberedActions() {
        $ac = new \SplObjectStorage();
        $ac[ActionKey::TotalHeader] = 'header0';
        $ac[ActionKey::TotalFooter] = 'footer0';
        $ac[ActionKey::DetailHeader] = 'header%n';
        $ac[ActionKey::DetailFooter] = 'footer%n';
        $ac[ActionKey::GroupBefore] = 'before%n';
        $ac[ActionKey::GroupFirst] = 'firstn%';
        $ac[ActionKey::GroupHeader] = 'header%n';
        $ac[ActionKey::GroupFooter] = 'footer%n';
        $ac[ActionKey::GroupLast] = 'last%n';
        $ac[ActionKey::GroupAfter] = 'after%n';
        $ac[ActionKey::DimNoData] = 'noData%n';
        $ac[ActionKey::DimDetail] = $this->detailName . '%n';
        $ac[ActionKey::DimNoGroupChange] = ["Current row in dimension %n didn't trigger a group change.", Action::ERROR];
        $this->numberedActions = $ac;
    }
  
}
