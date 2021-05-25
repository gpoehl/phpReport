<?php

declare(strict_types=1);

/**
 * Unit test of Configurator class
 */
use gpoehl\phpReport\Configurator;
use PHPUnit\Framework\TestCase;

class ConfiguratorTest extends TestCase {

    public function testBuildMethodsByGroupName() {
        $conf = new Configurator(['buildMethodsByGroupName' => 'ucfirst']);
        $this->assertSame('ucfirst', $conf->buildMethodsByGroupName);
        $conf = new Configurator(['buildMethodsByGroupName' => ' uCfIrSt ']);
        $this->assertSame('ucfirst', $conf->buildMethodsByGroupName);
        $conf = new Configurator(['buildMethodsByGroupName' => true]);
        $this->assertSame(true, $conf->buildMethodsByGroupName);
        $conf = new Configurator(['buildMethodsByGroupName' => false]);
        $this->assertSame(false, $conf->buildMethodsByGroupName);
        $this->expectException(InvalidArgumentException::class);
        $conf = new Configurator(['buildMethodsByGroupName' => 'invalid']);
    }

    public function testSetActions() {
        $conf = new Configurator(['actions' => ['init' => 'myInit']]);
        $this->assertSame('myInit', $conf->actions['init'][1]);
        $this->expectException(InvalidArgumentException::class);
        $conf = new Configurator(['actions' => ['invalidKey' => 'anyValue']]);
    }

    public function testSetGrandTotalName() {
        $conf = new Configurator(['grandTotalName' => 'validName']);
        $this->assertSame('validName', $conf->grandTotalName);
        $this->assertSame('validNameHeader', $conf->actions['totalHeader'][1]);
        $this->assertSame('validNameFooter', $conf->actions['totalFooter'][1]);
        $this->expectException(InvalidArgumentException::class);
        $conf = new Configurator(['grandTotalName' => 'invalid Name']);
    }

    public function testInvalidConfigurationParameter() {
        $this->expectException(InvalidArgumentException::class);
        new Configurator(['invalid' => 'invalid Paramemeter']);
    }

}
