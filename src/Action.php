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
 * Description of Action
 *
 * @author Günter
 */
class Action {

    /** The action key */
    public string $actionKey;

    /** @var Execute action only when true */
    public bool $execute;
    
    /** @var The action type describing the runTimeAction */
    public int $runTimeActionTyp;
   
    /** @var The real action to be executed */
    public $runTimeAction;
    
    /** @var The action type describing the given action */
    public $givenActionTyp; 
    
    /** @var The given action. Like a default action. The action to be executed
     * is set in setRunTimeAction method */
    public $givenAction;
    
    /** The target where for calling normal methods. */
    public $target;
    
    /** The target for calling prototye methods. */
    public Prototype $prototype;
    
    private $executor;

    public function __construct($target, Prototype $prototype, $actionKey, $actionParameter): ?array {
        $this->target = $target;
        $this->prototype = $prototype;
        $this->actionKey = $actionKey;
        [$this->givenActionTyp, $this->givenAction] = $actionParameter;
    }

    public function setRunTimeAction(int $callOption) {

        // Don't even call prototype action when action equals false
        if ($action === false) {
            $this->exectue = false;
            return;
        }

        $this->execute = true;
        $this->runTimeActionTyp = $this->givenActionTyp;

        // Warning and error typ will not be called in owner or prototye
        if ($this->givenActionTyp >= self::WARNING) {
            $this->runTimeAction = $this->givenAction;
            return;
        }

        // Call prototype regardless of the type. Prototype method is key
        // runTimeActionType get Callable
        if ($callOption === self::CALL_ALWAYS_PROTOTYPE) {
            $this->runTimeActionTyp = self::CALLABLE;
            $this->runTimeAction = [$this->prototype, $this->actionKey];
            return;
        }

        // String, closure or callable ([class, method] array)
        if ($this->givenActionTyp !== self::METHOD) {
            $this->runTimeAction = $this->givenAction;
            return;
        }

        // Normal method to be called in $target.
        // Execute only when method exists in $target or callOption equals Call_Always
        if ($callOption === self::CALL_ALWAYS || method_exists($this->target, $action)) {
            $this->runTimeAction = [$this->target, $this->givenAction];
            return;
        }

        // Call protoype only when callOption equals Call_Prototype 
        if ($callOption === self::CALL_PROTOTYPE) {
            $this->runTimeAction = [$this->prototype, $this->actionKey];
            return;
        }

        // no action is required
        $this->execute = false;
    }
    
    public function execute(){
        return $this->executor->execute();
    }

}
