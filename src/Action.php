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
 * Action to be executed when Report triggers an event
 */
class Action {

    // Action types 
    const STRING = 1;
    const CLOSURE = 2;
    const CALLABLE = 3;
    const METHOD = 4;
    const WARNING = 5;
    const ERROR = 6;

    /** The action key */
    public string $actionKey;

    /** @var Execute action only when true */
    public bool $execute;

    /** @var The action type describing the runTimeAction */
    private int $runTimeActionTyp;

    /** @var The real action to be executed */
    private $runTimeAction;

    /** @var The action type describing the given action */
    public int $givenActionTyp;

    /** @var The given action. Like a default action. The action to be executed
     * is set in setRunTimeAction method */
    public $givenAction;

   
    /**
     * 
     * @param string $actionKey
     * @param array $actionParameter consists of the actionTyp and the action
     */
    public function __construct(string $actionKey, array $actionParameter) {
        $this->actionKey = $actionKey;
        [$this->givenActionTyp, $this->givenAction] = $actionParameter;
    }

    /**
     * Set runtime action.
     * Runtime action is used to execute an action. 
     * Runtime action depends primarily on the call option and action type.
     * @param object $target Object which holds the methods to be called. 
     * @param Prototype $prototype The prototype object which executes prototype actions.
     * @param int $callOption The option how and where event actions will be executed. 
     */
    public function setRunTimeAction(object $target, ?Prototype $prototype, int $callOption): void {
        // Don't even call prototype action when action equals false
        if ($this->givenAction === false) {
            $this->execute = false;
        } else {
            // Set defaults. RunTimeActionTyp is usually the same as the givenActionTyp.
            $this->execute = true;
            // Given action tpyes  CLOSURE and METHOD are both CALLABLE RunTimeActionTyp.
            $this->runTimeActionTyp = ($this->givenActionTyp === self::CLOSURE || $this->givenActionTyp === self::METHOD) ?
                    self::CALLABLE : $this->givenActionTyp;

            // Warning and error actions will not be called in target or prototye object.
            if ($this->givenActionTyp >= self::WARNING) {
                $this->runTimeAction = $this->givenAction;
            } elseif ($callOption === Report::CALL_ALWAYS_PROTOTYPE) {
                // Call always prototype. Run time action type must be set to CALLABLE. 
                $this->runTimeActionTyp = self::CALLABLE;
                $this->runTimeAction = [$prototype, $this->actionKey];
            } elseif ($this->givenActionTyp !== self::METHOD) {
                // String, closure or callable ([class, method] array)
                $this->runTimeAction = $this->givenAction;
            } elseif ($callOption === Report::CALL_ALWAYS || method_exists($target, $this->givenAction)) {
                // CALL_ALWAYS means that a method in the target class will be called
                // no matter if the method really exists.
                $this->runTimeAction = [$target, $this->givenAction];
            } elseif ($callOption === Report::CALL_PROTOTYPE) {
                // Call protoype when method doesn't exists in target class and callOption equals Call_Prototype 
                $this->runTimeAction = [$prototype, $this->actionKey];
            } else {
                // None of the above conditions are true. So no action is required.
                $this->execute = false;
            }
        }
    }

    public function execute(...$params) {
        if ($this->execute) {
            switch ($this->runTimeActionTyp) {
                case self::STRING:
                    return ($this->runTimeAction);
                case self::CALLABLE:
                    return ($this->runTimeAction)(...$params);
                case self::WARNING:
                    trigger_error($this->runTimeAction . ' RowKey = ' . $params[1], E_USER_NOTICE);
                case self::ERROR:
                    throw new \RuntimeException($this->runTimeAction . ' RowKey = ' . $params[1]);
                default:
                    throw new \InvalidArgumentException("Action type {$this->runTimeActionTyp} is invalid");
            }
        }
    }

}
