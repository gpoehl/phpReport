<?php

declare(strict_types=1);

/**
 * Test Configurator class
 *
 * @author Guenter
 */

use gpoehl\backbone\Backbone;
use gpoehl\backbone\Configurator;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase {

    public function testLoadConfigurationFile() {
        $conf = new Configurator();
        $this->assertSame('ucfirst', $conf->buildMethodsByGroupName);
        $this->assertArrayHasKey('init', $conf->methods);
        $this->assertArrayHasKey('totalHeader', $conf->methods);
        $this->assertArrayHasKey('groupHeader', $conf->methods);
        $this->assertArrayHasKey('detail', $conf->methods);
        $this->assertArrayHasKey('groupFooter', $conf->methods);
        $this->assertArrayHasKey('totalFooter', $conf->methods);
        $this->assertArrayHasKey('close', $conf->methods);
        $this->assertArrayHasKey('fetchValues', $conf->methods);
        $this->assertArrayHasKey('fetchValues_n', $conf->methods);
    }

    public function testInstantiateClassWithConfiguration() {
        $config = [
            'buildMethodsByGroupName' => false,
            'methods' => [
                'groupHeader' => 'head%',
                'groupFooter' => 'foot%',
            ],
            'userConfig' => 'test user config'
        ];

        $conf = new Configurator($config);
        $this->assertSame(false, $conf->buildMethodsByGroupName);
        $this->assertArrayHasKey('groupHeader', $conf->methods);
        $this->assertArrayHasKey('groupFooter', $conf->methods);
//        var_dump($conf->methods['groupHeader']);
//        $this->assertSame([false, [Backbone::METHOD, 'head%']], $conf->methods['groupHeader']);
        $this->assertSame('foot%', $conf->methods['groupFooter']);
        $this->assertSame('test user config', $conf->userConfig);
    }
    
    public function testParameterIsString(){
//        $bb->test('aa');
//$bb->test(['aa', 'bb']);
//$bb->test(['aa\aa', 'bb']);
//$bb->test(['aa \aa', 'bb']);
//$bb->test(['aa\aa', 'b b']);
//$bb->test(['aa\aa', 'b%b']);
////$bb->test('__ab');
////$bb->test('aäÄ');
//
//echo '<br><br>Invalid names:';
//$bb->test(['aa\aa', 'bb', 'cc']);
//$bb->test(['aa\aa', ['bb', 'cc']]);
// invalid method names
//$bb->test('1ab');
//$bb->test('a<b');
//$bb->test('<ab');
//$bb->test('a b');
//$bb->test('a.b');
//$bb->test('aäÄ!');
//$bb->test('$aäÄ');
//$bb->test('a$äÄ');
//$bb->test('aäÄ)');
//$bb->test('aäÄ(');
//$bb->test("ab\cd");
//$bb->test('ab\cd');
//$bb->test("ab/cd");
//$bb->test('ab/cd');
//echo $bb->ab();
//die();
//$bb->bb->setCallOption(Backbone::CALL_ALWAYS_PROTOTYPE);
    }

}
