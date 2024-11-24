<?php

declare(strict_types=1);

/**
 * Unit test of Configurator class
 */
use gpoehl\phpReport\Configurator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConfiguratorTest extends TestCase {

    #[DataProvider('namesProvider')]
    public function testNames(string $name, string $value, string $expected): void {
        $conf = new Configurator([$name => $value]);
        $this->assertSame($expected, $conf->$name,);
    }

    public static function namesProvider(): array {
        return [
            'Name of total variable'=>['totalName', 'testTotal', 'testTotal'],
            'Name of detail variable'=>['detailName', 'testDetail', 'testDetail'],
        ];
    }

    #[DataProvider('invalidNamesProvider')]
    public function testInvalidNames(string $name, string $value): void {
        $this->expectException(InvalidArgumentException::class);
        new Configurator([$name => $value]);
    }

    public static function invalidNamesProvider(): array {
        return [
            'Invalid name of total variable'=>['totalName', 'test Total'],
            'Invalid name of detail variable'=>['detailName', 'test Detail'],
        ];
    }

    #[DataProvider('emptyNamesProvider')]
    public function testEmptyNames(string $name, string $value): void {
        $this->expectException(InvalidArgumentException::class);
        new Configurator([$name => $value]);
    }

    public static function emptyNamesProvider(): array {
        return [
            ['totalName', ''],
            ['detailName', ''],
        ];
    }

    public function testInvalidConfigurationParameter() {
        $this->expectException(InvalidArgumentException::class);
        new Configurator(['invalid' => 'invalid Parameter']);
    }

    #[DataProvider('actionsProvider')]
    #[DataProvider('configFileProvider')]
    public function testActions(array $params, string $expected): void {
        $conf = new Configurator($params);
        $this->assertSame($expected, $conf->actions[gpoehl\phpReport\Actionkey::GroupHeader]);
    }

    public static function actionsProvider(): array {
        return [
            'Get default' => [['configFile' => false], 'header%S'],
            'False nameded actions' => [['useNumberedActions' => false], 'header%S'],
            'Use numbered actions' => [['useNumberedActions' => true], 'header%n'],
            'Alter default actions' => [['useNumberedActions' => true,
                    'numberedActions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => 'test%s']], 'test%s'],
            'actions has highest priority' => [['useNumberedActions' => true,
                    'numberedActions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => 'test%s'],
                    'actions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => '%Stest'],
                ], '%Stest'],
        ];
    }

    public static function configFileProvider(): array {
        return [
            'CFP Get default' => [[], 'header%S'],
            'CFP False nameded actions' => [['useNumberedActions' => false], 'header%S'],
            'CFP Use numbered actions' => [['useNumberedActions' => true], 'header%n'],
            'CFP Alter default actions' => [['useNumberedActions' => true,
                    'numberedActions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => 'test%s']], 'test%s'],
            'CFP actions has highest priority' => [['useNumberedActions' => true,
                    'numberedActions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => 'test%s'],
                    'actions' => [gpoehl\phpReport\Actionkey::GroupHeader->name => '%Stest'],
                ], '%Stest'],
        ];
    }

    #[DataProvider('configFileNameProvider')]
    public function testConfigFileName($name, string $expected): void {
        $conf = new Configurator(['configFilename' => __Dir__ . $name]);
//        $this->assertSame($expected, __Dir__);
        $this->assertSame($expected, $conf->totalName);
    }

    public static function configFileNameProvider(): array {
        return [
            'Get default' => ['/config/config1.php', 'config1'],
        ];
    }
}
