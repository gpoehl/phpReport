<?php

declare(strict_types=1);

require_once __DIR__ . '/Foo.php';

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\Action;
use gpoehl\phpReport\Prototype;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{

    public object $prototype;
    static object $target;

    public static function setUpBeforeClass(): void {

        // Simulatate default target class
        self::$target = new class {

            public function __call($name, $args) {
                return $name;
            }

            public function header(... $args) {
                return 'header';
            }
        };
    }

    public function setUp(): void {
        $stub = $this->getMockBuilder(Prototype::class)
                ->disableOriginalConstructor()
                ->getMock();
        $stub->method('groupHeader')
                ->will($this->returnArgument(0));
        $this->prototype = $stub;
    }

    /**
     * @dataProvider actionStringProvider
     * @dataProvider actionClosureProvider
     * @dataProvider actionCallableProvider
     * @dataProvider actionMethodProvider
     * @dataProvider actionMethodNotExistsProvider
     */
    public function testExecuter($expected, $key, $actionParameter, $callOption) {
        $action = new Action($key, $actionParameter);
        $action->setRuntimeTarget(self::$target, $this->prototype, $callOption);
        $this->assertSame($expected, $action->execute('abc', 'row', 1, 0));
    }

    public function actionStringProvider() {
        $expect = 'xyz';
        $actionParam = ['xyz', false];
        return [
            'string' => ['ab cd', 'groupHeader', 'ab cd', Report::CALL_EXISTING],
            'string0' => [$expect, 'groupHeader', $actionParam, Report::CALL_EXISTING],
            'string1' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS],
            'string2' => [$expect, 'groupHeader', $actionParam, Report::CALL_PROTOTYPE],
            'string3' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'string4' => ['abc', 'groupHeader', $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public function actionClosureProvider() {
        $actionParam = fn($p1, $p2, $p3, $p4) => ($p1);
        $expect = 'abc';
        return [
            'closure0' => [$expect, 'groupHeader', $actionParam, Report::CALL_EXISTING],
            'closure1' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS],
            'closure2' => [$expect, 'groupHeader', $actionParam, Report::CALL_PROTOTYPE],
            'closure3' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'closure4' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public function actionCallableProvider() {
        $actionParam = [new Foo(), 'foo'];
        $expect = 'funcFoo';
        return [
            'callable0' => [$expect, 'groupHeader', $actionParam, Report::CALL_EXISTING],
            'callable1' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS],
            'callable2' => [$expect, 'groupHeader', $actionParam, Report::CALL_PROTOTYPE],
            'callable3' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'callable4' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public function actionMethodProvider() {
        $actionParam = 'header';
        $expect = 'header';
        return [
            'method0' => [$expect, 'groupHeader', $actionParam, Report::CALL_EXISTING],
            'method1' => [$expect, 'groupHeader', $actionParam, Report::CALL_ALWAYS],
            'method2' => [$expect, 'groupHeader', $actionParam, Report::CALL_PROTOTYPE],
            'method3' => ['abc', 'groupHeader', $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'method4' => ['abc', 'groupHeader', $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    public function actionMethodNotExistsProvider() {
        $actionParam = 'headerNotExist';
        return [
            'nonExistingMethod0' => [Null, 'groupHeader', $actionParam, Report::CALL_EXISTING],
            'nonExistingMethod1' => ['headerNotExist', 'groupHeader', $actionParam, Report::CALL_ALWAYS],
            'nonExistingMethod2' => ['abc', 'groupHeader', $actionParam, Report::CALL_PROTOTYPE],
            'nonExistingMethod3' => ['abc', 'groupHeader', $actionParam, Report::CALL_ALWAYS_PROTOTYPE],
            'nonExistingMethod4' => ['abc', 'groupHeader', $actionParam, Report::CALL_ALL_PROTOTYPE],
        ];
    }

    /**
     * @dataProvider triggerProvider
     */
    public function testWarning($expected, $key, $actionParameter) {
        $this->expectWarning();
        $this->expectWarningMessage($expected);
        $action = new Action($key, [$actionParameter, Action::WARNING]);
        $action->setRuntimeTarget(self::$target, $this->prototype, Report::CALL_EXISTING);
        $action->execute('abc', 'row', 1, 0);
    }

    /**
     * @dataProvider triggerProvider
     */
    public function testNotice($expected, $key, $actionParameter) {
        $this->expectNotice();
        $this->expectNoticeMessage($expected);
        $action = new Action($key, [$actionParameter, Action::NOTICE]);
        $action->setRuntimeTarget(self::$target, $this->prototype, Report::CALL_EXISTING);
        $action->execute('abc', 'row', 1, 0);
    }

    /**
     * @dataProvider triggerProvider
     */
    public function testError($expected, $key, $actionParameter) {
        $this->expectError();
        $this->expectExceptionMessage($expected);
        $action = new Action($key, [$actionParameter, Action::ERROR]);
        $action->setRuntimeTarget(self::$target, $this->prototype, Report::CALL_EXISTING);
        $action->execute('abc', 'row', 1, 0);
    }

    public function triggerProvider() {
        return [
            'Warning string' => ['Warning message', 'groupHeader', 'Warning message'],
            'Warning method' => ['header', 'groupHeader', 'header'],
            'Warning closure' => ['abc', 'groupHeader', fn($p1, $p2, $p3, $p4) => ($p1)],
            'Warning callable' => ['funcFoo', 'groupHeader', [new Foo(), 'foo']],
        ];
    }

    public function actionNoticeAndErrorProvider() {
        return [
            'String' => ['Error or warning message', 'groupHeader', 'Error or warning message'],
            'Method' => ['header', 'groupHeader', 'header'],
            'Closure' => ['abc', 'groupHeader', fn($p1, $p2, $p3, $p4) => ($p1)],
            'Callable' => ['funcFoo', 'groupHeader', [new Foo(), 'foo']],
        ];
    }

}
