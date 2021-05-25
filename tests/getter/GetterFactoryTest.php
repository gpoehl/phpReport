<?php

declare(strict_types=1);

/**
 * Unit test of GetterFactory class and related getter methods.
 */
require_once __DIR__ . '/../Foo.php';

use gpoehl\phpReport\getter\GetProperty;
use gpoehl\phpReport\getter\GetRowProperty;
use gpoehl\phpReport\getter\GetterFactory;
use PHPUnit\Framework\TestCase;

class GetterFactoryTest extends TestCase
{
     /**
     * @dataProvider SourceProvider
     * @dataProvider externalSourceProvider
     */
    public function testVerifySource($expected, $source, ... $params) {
        $this->assertTrue(GetterFactory::verifySource($source, $params));
    }
    
    /**
     * @dataProvider sourceProvider
     */
    public function testObjectValue($expected, $source, ...$params) {
        $foo = new Foo();
        $objFactory = new GetterFactory(true, null, $foo::class);
        $getter = $objFactory->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue($foo, 'rowKey'));
    }

    public function sourceProvider() {
        return [
            'base getter' => ['nobody', new GetRowProperty('name')],
            'closure' => ['nobody rowKey', fn($row, $rowKey) => ($row->name . ' ' . $rowKey)],
            'closure and param' => ['nobody is perfect', fn($row, $rowKey, $param) => ($row->name . ' ' . $param), 'is perfect'],
            
            'closure explicit with row' => ['nobody is perfect', [fn($row, $rowkey, ... $param) => ($row->name . ' ' . implode(' ', $param)), null, true], 'is', 'perfect'],   
            'closure no row' => [10, [fn(... $param) => (array_sum($param))], 2,3,5],
            'closure no row long' => [10, [fn(... $param) => (array_sum($param)), null, false], 2,3,5],
          
            'row obj property' => ['nobody', 'name'],
            'row obj method' => ['NOBODY', ['getUName']],
            'row obj method medium' => ['NOBODY', ['getUName', null]],
            'row obj method long' => ['NOBODY', ['getUName', null, null]],
            'row obj method param' => ['pubProp', ['getProperty'], 'pubProp'],
            'row obj method params' => ['nobody is perfect', ['say'], 'is', 'perfect'],
            
            'row static method' => [10, ['staticAdd'],2, 3, 5], 
            'row static method with source[1]' => [10, ['staticAdd', null],2, 3, 5], 
            'row static method with full' => [10, ['staticAdd', null, null], 2, 3, 5],  
            'row static method with row' => ['Hello nobody', ['sayHello', null, true]],
            
            'row constant' => ['pubConst', ['PUBCONST', null, 'const']],
            
            'row static property same name as const' => ['pubStat', ['pubStat', null, 'stat']],
            'row const same name as static prop' => ['constPubStat', ['pubStat', null, 'const']],
            
            'row property same name as const and stat' => ['pubProp', 'pubProp'],
            'row constant same name as property' => ['constPubProp', ['pubProp', null, 'const']],

            'row property same name as method' => ['foo', 'foo'],
            'row method same name as property' => ['funcFoo', ['foo']],
        ];
    }
    
      /**
     * @dataProvider externalSourceProvider
     */
    public function testExternalValue($expected, $source, ...$params) {
        $bar = new class(){public $name = 'anonymous';};
        $objFactory = new GetterFactory(true, null, foo::class);
        $getter = $objFactory->getGetter($source, $params);
        $this->assertSame($expected, $getter->getValue($bar, 'rowKey'));
    }
    
      public function externalSourceProvider() {
        $foo = new Foo();
        return [
            'base getter' => ['nobody', new GetProperty([$foo, 'name'])],
          
            'row obj property' => ['nobody', ['name', $foo]],
            'row obj method' => ['NOBODY', ['getUName', $foo, false]],
            'row obj method param' => ['pubProp', ['getProperty', $foo, false], 'pubProp'],
            
            'row static method' => [10, ['staticAdd', $foo, false],2, 3, 5], 
            'row static method with row' => ['Hello anonymous', ['sayHello', $foo, true]],
            
            'row constant' => ['pubConst', ['PUBCONST', $foo, 'const']],
        ];
    }
    
    public function testDefaultTarget() {
        $bar = new class(){public $name = 'anonymous';};
        $objFactory = new GetterFactory(true, new foo('target'), null);
        $getter = $objFactory->getGetter(['name', true], []);
        $this->assertSame('target', $getter->getValue($bar, 'rowKey'));
        $getter = $objFactory->getGetter(['say', true, false], ['is', 'default']);
        $this->assertSame('target is default', $getter->getValue($bar, 'rowKey'));
    }
    
     /**
     * @dataProvider InvalidSourceExceptionProvider
     */
    public function testInvalidSourceException($expected, $source, ... $params) {
        $objFactory = new GetterFactory(true, new foo('target'), null);
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessageMatches($expected);
        $getter = $objFactory->getGetter($source, $params);
    }
    
    public function InvalidSourceExceptionProvider() {
        return [
            'Class member (no property) is not const or stat' => ['/^Invalid source selector.*ABC/', ['ABC',null,'xx']],
            ];
    }

     /**
     * @dataProvider SourceExceptionProvider
     */
    public function testVerifySourceException($expected, $source, ... $params) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessageMatches($expected);
        GetterFactory::verifySource($source, $params);
    }
    
    public function SourceExceptionProvider() {
        return [
            'Object not a GetValueInterface' =>['/^Source object must implement the GetValueInterface/', new class(){}, 1, 2],
            'Invalid scalar source' =>['/^Scalar source is not/', 3.7, 1, 2],
            'empty array' => ['/empty array/i', [], [1, 2]], 
            'Name element is Null' =>['/^Source name element is null/', [null, 'ABC', false]],
            'Source with more than 3 elements' => ['/4 elements given, max. 3 expected/', ['a',2,3,4]],
            'Closure with 4 source elements' =>['/4 elements given, max. 3 expected/', [fn() => (null),'x', 'y', 'z'], 1, 2],     
            'Class member (no property) is not const or stat' => ['/^Invalid source param/', ['ABC',null,'xx']],
            ];
    }
    
      /**
     * @dataProvider SourceWarningProvider
     */
    public function testVerifySourceWarning($expected, $source, ... $params) {
        $this->expectWarning();
        $this->expectWarningMessageMatches($expected);
        GetterFactory::verifySource($source, $params);
    }
    
    
    public function SourceWarningProvider() {
       return [
           'Row field has parameters' =>['/^Parameters will be ignored.*ABC\'\.$/', 'ABC', 1, 2],
           'Closure with 2 source elements' =>['/^Second .* will be ignored/', [fn() => (null),'x'], [1, 2]],
          
           'Object or class property has parameter null' =>['/^Parameters will be ignored.*object property.*ABC\'\.$/', ['ABC', 'className', null], null],
           'Object or class property has parameter' =>['/^Parameters will be ignored.*object property.*ABC\'\.$/', ['ABC', 'className', null], 1],
           'Const has param' => ['/^Parameters will be ignored.*constant.*ABC\'\.$/', ['ABC',null,'const'], 1],
            'Const has param' => ['/^Parameters will be ignored.*static.*ABC\'\.$/', ['ABC',null,'stat'], 1],
           ];
    }
    

    /**
     * @dataProvider sheetProvider
     */
    public function testGetValueForSheet($expected, $keySource, $source, ?array $keyParams=[] , ...$params) {
        $objFactory = new GetterFactory(true);
        $getter = $objFactory->getSheetGetter($keySource, $source, $keyParams, $params);
        $this->assertSame($expected, $getter->getValue(new foo(value: 789), 123));
    }

    public function sheetProvider() {
        return [
            'No params' => [['nobody'=>123], 'name', fn($row, $rowKey) => ($rowKey)],
            'With keyParams' => [[789 => 123], ['getProperty'], fn($row, $rowKey)=>($rowKey), ['value']],
            'With sourceParams' => [['nobody' => 789], 'name', ['getProperty'], [], 'value'],
        ];
    }
    
}
