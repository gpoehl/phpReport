<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright © Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport;


/**
 * Action to be executed when Report triggers an event
 */
final class Action
{

    // pattern to check that method or variable names are valid.
    // patternPercent extends pattern to accept also the % sign
    static $pattern =   "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/";
    static $patternPercent = "/^[a-zA-Z_%\x7f-\xff][a-zA-Z0-9_%\x7f-\xff]*$/";

    // Action target keys  
    const NOTHING = 0;
    const STRING = 1;
    const METHOD = 2;
    const CLOSURE = 3;
    const CALLABLE = 4;
    // Action kind  
    const OUTPUT = 0;
    const WARNING = \E_USER_WARNING;
    const NOTICE = \E_USER_NOTICE;
    const ERROR = \E_USER_ERROR;

    /* @var $kind Output or error level. When !== OUTPUT an trigger_error($message, $kind) should be invoked. */
    public int $kind = self::OUTPUT;
    
    /* @var $targetKey Derived from the given target. When !== STRING the target
      runtimeTarget should be used as an function. */
    public int $targetKey = self::NOTHING;
    /* $runtimeTarget The target after taking the callOption into account */
    public $runtimeTarget;

    /**
     * @param string $key The action key. Used to call methods in prototype class. 
     * @param mixed $outputKey Value of $actionKeyMapper in output class. Small performance
     * booster to pass this key instead of $key.
     * @param $level The related group level.
     * @param mixed $target The target action. Usually a method name. Closure,
     * callable or a string is also possible. False to never invoke the action.
     * @param int|null $kind One of the action kinds (output, warning , notice, error).
     * Defaults to output. 
     * @param string|null $replacement Optinal replacement for the % sign in $target
     * @throws InvalidArgumentException2
     */
    public function __construct(public readonly ActionKey $key, public $outputKey, public $target, public ?string $name ='', public ?int $id = 0) {
        if ($target !== false) {
            $this->targetKey = $this->detectTargetKey();
            if (!in_array($this->kind, [self::OUTPUT, self::NOTICE, self::WARNING, self::ERROR])) {
                throw new \InvalidArgumentException("Invalid action kind '$this->kind'.");
            }
        }
    }

    /**
     * Detect the target key related to the given target
     * @throws InvalidArgumentException2
     */
    private function detectTargetKey(): int {
        if (is_array($this->target)) {
            if (count($this->target) !== 2) {
                throw new \InvalidArgumentException("Action target array must have 2 elements.");
            }

            // When the second element is an integer it's a trigger_error
            if (is_integer($this->target[1])) {
                $this->kind = $this->target[1];
                $this->target = $this->target[0];
            }
        }

        if ($this->target instanceof \Closure) {
            return self::CLOSURE;
        }

        if (is_array($this->target)) {
            if (count($this->target) !== 2) {
                throw new \InvalidArgumentException("Action target array must have 2 elements.");
            }
            // When second element equals false action[0] is handled as string.
            if ($this->target[1] === false) {
                $this->target = $this->replaceNameAndID($this->target[0]);
                return self::STRING;
            }
        }

      $this->target = $this->replaceNameAndID($this->target);

        return match (true) {
            $this->target === false => self::NOTHING,
            is_array($this->target) => self::CALLABLE,
            self::isNameValid($this->target) => self::METHOD,
            default => self::STRING,
        };
    }
    
    private function replaceNameAndID($value) {
        if (is_array($value)){
            $value[1] = str_replace(['%n', '%s', '%S'], [(string) $this->id, $this->name, ucfirst($this->name)], $value[1]);
            return $value;
        } else {
             return str_replace(['%n', '%s', '%S'], [(string) $this->id, $this->name, ucfirst($this->name)], $value);
        }
    }


    /**
     * Set the target to be executed when an event occurs.
     * The given target might be redirected depending on the $callOption.
     *
     * @param object $target Object which holds the methods to be called. 
     * @param $prototype The prototype object which executes prototype actions.
     * @param $runtTimeOption The option how and where event actions will be executed. 
     */
    public function setRunTimeTarget(object $target, ?PrototypeInterface $prototype, RuntimeOption $runtimeOption) {
        
        $this->runtimeTarget = match (true) {
            $this->targetKey === self::NOTHING => null,
            $this->targetKey === self::CALLABLE => $this->target,
            // Prototype
            $runtimeOption === RuntimeOption::PrototypeAll,
            $runtimeOption === RuntimeOption::PrototypeMethods && $this->targetKey === self::METHOD => [$prototype, $this->key->name],
            // Don't duplicate strings
            $this->targetKey === self::STRING => true,
            $this->targetKey === self::CLOSURE || $this->targetKey === self::CALLABLE => $this->target,
            // Now we have only a method
            $runtimeOption === RuntimeOption::Magic,
            method_exists($target, $this->target) => [$target, $this->target],
            $runtimeOption === RuntimeOption::Prototype => [$prototype, $this->key->name],
            default => null
        };
    }

    /**
     * 
     * @param type $value
     * @param bool $allowReplacement Repclaces % sign when $value represents a method name
     * @return bool
     */
    public static function isNameValid($value, bool $allowReplacement = false): bool {
        return ($allowReplacement) ? (bool) preg_match(self::$patternPercent, $value) :
                (bool) preg_match(self::$pattern, $value);
    }

}
