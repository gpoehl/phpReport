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

/**
 * Test class having all types of properties and methods
 * Parameter $row in methods must have an property / item called 'name'
 */
class Foo
{

    public $pubProp = 'pubProp';
    protected $protProp = 'protProp';
    private $privProp = 'privProp';
    
    public static $pubStat = 'pubStat';
    protected static $protStat = 'protStat';
    private static $privStat = 'privStat';

    public const PUBCONST = 'pubConst';
    protected const PROTCONST = 'protConst';
    private const PRIVCONST = 'privConst';
    
    // Const with same name as properties / static properties
    public const pubProp = 'constPubProp';
    public const pubStat = 'constPubStat';
    
    public string $closure;
    
    // Property and method have the same name
    public $foo = 'foo';
    public function foo() {
        return 'funcFoo';
    }

    // Declare new property on the fly
    public function __construct(public string $name = 'nobody', $value = 'dear') {
        $this->value = $value;
    }

    // Method without parameter
    public function getUName() {
        return strtoupper($this->name);
    }
    
    // Method with one parameter
    public function getProperty($propertyName) {
        return $this->$propertyName;
    }
    
     // Method with more parameters
    public function say(... $values) {
         return $this->name . ' ' . implode (' ', $values);
    }
    
    // method expecting $row and $rowKey
    public function withRow(array|object $row, string $rowKey) {
        $row = (object) $row;
        return $this->name . ' ' . $row->name . ' ' + $rowKey;
    }

    public function sayFromRow(array|object $row, $rowKey, $word = 'Hello') {
        $row = (object) $row;
        return $word . ' ' . $this->name . ' and ' . $row->name;
    }
    
    /**
     * Static methods
     */

    public static function staticNoParm() {
        return 'stat method';
    }
    
    public static function staticAdd(... $values) {
        return array_sum($values);
    }
    
    public static function sayHello(array|object $row, $rowKey) {
        $row = (object) $row;
        return 'Hello ' . $row->name;
    }

    public static function saySomething(array|object $row, $rowKey, $word = 'Goodbye') {
        $row = (object) $row;
        return $word . ' ' .self::$pubStat . ' and ' . $row->name;
    }
    
//    public function x (){
//        return self::PUBCONST;
//    }
//    public static function y (){
//        return self::PUBCONST;
//    }

}
$t = new Foo();
echo $t->staticAdd(1,7);
echo constant('foo::PUBCONST');