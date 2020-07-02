<?php

declare(strict_types=1);

/**
 * Unit test of Helper class
 */

use gpoehl\phpReport\Action;
use gpoehl\phpReport\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase {

    public function testBuildMethodAction() {
        $this->assertSame([Action::STRING, 'x x'], Helper::buildMethodAction('x x', 'init', false));
        $this->assertSame([Action::METHOD, 'xx'], Helper::buildMethodAction('xx', 'init', false));
        $this->assertSame(Action::CLOSURE, Helper::buildMethodAction(function() {
                    
                }, 'init', false)[0]);
        $this->assertSame([Action::CALLABLE, ['a', 'b']], Helper::buildMethodAction(['a', 'b'], 'init', false));
        $this->assertSame([Action::CALLABLE, ['a\b', 'b']], Helper::buildMethodAction(['a\b', 'b'], 'init', false));
        $this->assertSame([Action::METHOD, 'a'], Helper::buildMethodAction(['a'], 'init', false));
        $this->assertSame([Action::STRING, 'a'], Helper::buildMethodAction(':a', 'init', false));
    }

    public function testBuildInvalidMethodAction() {
        $this->expectException(\InvalidArgumentException::class);
        Helper::buildMethodAction(['a\b'], 'init', false);
    }

    public function testBuildInvalidMethodActionForCallable() {
        $this->expectException(\InvalidArgumentException::class);
        Helper::buildMethodAction(['a', 'b\c'], 'init', false);
    }

    public function testIsValidNameReturnsTrue() {
        $this->assertTrue(Helper::isValidName('a'));
        $this->assertTrue(Helper::isValidName('äöüÄÖÜß'));
        $this->assertTrue(Helper::isValidName('__a'));
        $this->assertTrue(Helper::isValidName('a%', true), '% sign allowed');
    }

    public function testIsValidNameReturnsFalse() {
        $this->assertFalse(Helper::isValidName('a b'), 'has blank');
        $this->assertFalse(Helper::isValidName('1a'), 'start with a number');
        $this->assertFalse(Helper::isValidName('a%'), '% sign not allowed');
        $this->assertFalse(Helper::isValidName('a\v'), 'has backslash');
        $this->assertFalse(Helper::isValidName('a/v'), 'has slash');
    }

    /**
     * @dataProvider callReplacePercentProvider
     */
    public function testReplacePercent(array $action, $expected) {
        $this->assertSame($expected, Helper::replacePercent('rep_', $action)[1]);
    }

    public function callReplacePercentProvider() {
        return [
            [[Action::METHOD, '%a'], 'rep_a'],
            [[Action::CALLABLE, '%a'], 'rep_a'],
            [[Action::STRING, '%a'], 'rep_a'],
            [[Action::CLOSURE, '%a'], '%a']
        ];
    }

}
