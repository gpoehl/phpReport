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
 * Enum for runtime option
 * Used to redirect calls to a prototye class or to call magic funtions.
 * 
 *  CALL_EXISTING = 0;          // Call methods in owner class only when implemented. Default.
 *  CALL_ALWAYS = 1;            // Call also not existing methods in owner class. Allows using magic function calls.
 *  CALL_PROTOTYPE = 2;         // Call prototype for methods not implemented in owner class.
 *  CALL_ALWAYS_PROTOTYPE = 3;  // Call always prototype even when method exists in owner class.
 *  CALL_ALL_PROTOTYPE = 4;     // Call prototype for all actions which are not callables and action is not false.

 * 
 * 
 */
enum RuntimeOption: int
{
    case Default = 0;
    case Magic = 1;
    case Prototype = 2;
    Case PrototypeMethods = 3;
    Case PrototypeAll = 4;

    public function isPrototype():bool{
       return ($this->value >= 2) ;
    }
    
    public function hint():bool{
       return match($this) {
            self::Default => 'Call only existing methods.',
            self::Magic => 'Call also non existing methods. Use _magic().',
            self::Prototype => 'Prototype methods not implemented in target class.',
            self::PrototypeMethods => 'Prototype all methods.',
            self::PrototypeAll => 'Prototype all actions except callables and actions methods eqals false.',
        };
    }
}

