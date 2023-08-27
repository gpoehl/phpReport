<?php

declare(strict_types=1);

/**
 * Unit test of Action class
 */
use gpoehl\phpReport\Action;
use gpoehl\phpReport\Prototype;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{

    public object $prototype;
    static object $targetObj;

    public static function setUpBeforeClass(): void {

        // Simulatate default target class
        self::$targetObj = new class {

            public function __call($name, $args) {
                
            }

            public function header(... $args) {
                
            }
        };
    }

    /**
     * Mock the prototype class. 
     * Run all tests with method 'groupHeader'.
     * Mocked prototype class will always return the first parameter. 
     */
    public function setUp(): void {
        $stub = $this->getMockBuilder(Prototype::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->prototype = $stub;
    }

    /**
     * @dataProvider actionStringProvider
     * @dataProvider actionClosureProvider
     * @dataProvider actionCallableProvider
     * @dataProvider actionMethodProvider
     * @dataProvider actionMethodNotExistsProvider
     * @dataProvider actionFalseProvider
     * @dataProvider triggerProvider
     */
    public function testExecuter($expectTargetKey, $expect, $target, $callOption, $error = null) {
        $action = new Action('groupHeader', null, 1, $target);
        $action->setRuntimeTarget(self::$targetObj, $this->prototype, $callOption);
        $this->assertEquals($expectTargetKey, $action->targetKey,);
        if (is_array($expect)) {
            // Replace true and false with class to be called 
            $expect = match ($expect[0]) {
                null => $expect[1],
                true => [$this->prototype, $expect[1]],
                false => [self::$targetObj, $expect[1]],
                default => $expect
            };
        }
        if (($action->runtimeTarget === true) && $action->targetKey === Action::STRING) {
            // String but not in prototype
            $this->assertEquals($expect, $action->target);
        } else {
            $this->assertEquals($expect, $action->runtimeTarget);
        }
        if ($error === null) {
            $this->assertEquals(Action::OUTPUT, $action->kind);
        } else {
            $this->assertEquals($error, $action->kind);
        }
    }

    public static function actionStringProvider() :array{
        $key = Action::STRING;
        $expectPrototye = [true, 'groupHeader'];
        $expect = 'could_be_a_method';
        $actionParam = [$expect, false];
        return [
            'string1' => [$key, 'ab cd', 'ab cd', Report::CALL_EXISTING],
            'string2' => [$key, $expect, $actionParam, Report::CALL_EXISTING],
            'string3' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'string4' => [$key, $expect, $actionParam, Report::CALL_PROTOTYPE],
            'string5' => [$key, $expect, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'string6' => [$key, $expectPrototye, $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public static function actionClosureProvider() :array{
        $key = Action::CLOSURE;
        $actionParam = fn($p1, $p2, $p3, $p4) => ($p1);
        $expect = [null, $actionParam];
        return [
            'closure1' => [$key, $expect, $actionParam, Report::CALL_EXISTING],
            'closure2' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'closure3' => [$key, $expect, $actionParam, Report::CALL_PROTOTYPE],
            'closure4' => [$key, $expect, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'closure5' => [$key, [true, 'groupHeader'], $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public static function actionCallableProvider():array {
        $key = Action::CALLABLE;
        $actionParam = $expect = ['foo', 'bar'];
        return [
            'callable1' => [$key, $expect, $actionParam, Report::CALL_EXISTING],
            'callable2' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'callable3' => [$key, $expect, $actionParam, Report::CALL_PROTOTYPE],
            'callable4' => [$key, $expect, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'callable5' => [$key, $expect, $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public static function actionMethodProvider() :array{
        $actionParam = 'header';
        $key = Action::METHOD;
        $expectPrototye = [true, 'groupHeader'];
        $expect = [false, $actionParam];
        return [
            'method1' => [$key, $expect, $actionParam, Report::CALL_EXISTING],
            'method2' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'method3' => [$key, $expect, $actionParam, Report::CALL_PROTOTYPE],
            'method4' => [$key, $expectPrototye, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'method5' => [$key, $expectPrototye, $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public static function actionMethodNotExistsProvider() :array{
        $actionParam = 'headerNotExist';
        $key = Action::METHOD;
        $expectPrototye = [true, 'groupHeader'];
        $expect = [false, $actionParam];
        return [
            'NoMmethod1' => [$key, Null, $actionParam, Report::CALL_EXISTING],
            'NoMmethod2' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'NoMmethod3' => [$key, $expectPrototye, $actionParam, Report::CALL_PROTOTYPE],
            'NoMmethod4' => [$key, $expectPrototye, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'NoMmethod5' => [$key, $expectPrototye, $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    // Parameter false will never be executed
    public static function actionFalseProvider() :array {
        $actionParam = false;
        $key = Action::NOTHING;
        $expectPrototye = Null;
        $expect = false;
        return [
            'False1' => [$key, $expect, $actionParam, Report::CALL_EXISTING],
            'False2' => [$key, $expect, $actionParam, Report::CALL_ALWAYS],
            'False3' => [$key, $expectPrototye, $actionParam, Report::CALL_PROTOTYPE],
            'False4' => [$key, $expectPrototye, $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'False5' => [$key, $expectPrototye, $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public static function triggerProvider() :array{
        $error = Action::ERROR;
        $callAction = Report::CALL_ALWAYS;
        return [
            'String' => [Action::STRING, 'Warning message', ['Warning message', $error], $callAction, $error],
            'StringArr' => [Action::STRING, 'WarningMessage', [['WarningMessage', false], $error], $callAction, $error],
            'Method' => [Action::METHOD, [false, 'header'], ['header', $error], $callAction, $error],
            'Closure' => [Action::CLOSURE, fn($p1) => ($p1), [fn($p1) => ($p1), $error], $callAction, $error],
            'Callable' => [Action::CALLABLE, ['class', 'foo'], [['class', 'foo'], $error], $callAction, $error],
        ];
    }

    public function testInvalidActionKind() :void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid action kind '999'.");
        new Action('init', null, 1, ['abc', 999]);
    }

    public function testInvalidArrayElements() :void{
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Action target array must have 2 elements.");
        new Action('init', null, 1, ['abc', 999, 44]);
    }

    public function testIsValidNameReturnsTrue():void {
        $this->assertTrue(Action::isNameValid('a'));
        $this->assertTrue(Action::isNameValid('äöüÄÖÜß'));
        $this->assertTrue(Action::isNameValid('__a'));
        $this->assertTrue(Action::isNameValid('a%', true), '% sign allowed');
    }

    public function testIsValidNameReturnsFalse():void {
        $this->assertFalse(Action::isNameValid('a b'), 'has blank');
        $this->assertFalse(Action::isNameValid('1a'), 'start with a number');
        $this->assertFalse(Action::isNameValid('a%'), '% sign not allowed');
        $this->assertFalse(Action::isNameValid('a\v'), 'has backslash');
        $this->assertFalse(Action::isNameValid('a/v'), 'has slash');
    }

}
