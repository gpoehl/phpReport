<?php

declare(strict_types=1);

/**
 * Unit test of ArrayDataHandler class
 */
use gpoehl\phpReport\ArrayDataHandler;
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ArrayDataHandlerTest extends TestCase {

    public $stack;

    /**
     * Setup method must be called by tests when neede.
     * Default SetUp() not used because Dimension must be instantiated with
     * different parameters.
     * @return void
     */
    public function mySetUp(): void {
        $dim = new Dimension(1,'array');
        $this->stack = $dim->dataHandler;
        $dim->target='Testclass';
    }

    /**
     * @dataProvider groupSources
     */
    public function testGetGroupValues($source, $expected, ...$params) {
        $this->mySetUp();
        $row = ['A' => 'first', 'B' => 3, 5, 7];
        if ($source !== null) {
            $this->stack->addGroup($source, $params);
        }
        $this->assertSame($expected, $this->stack->getGroupValues($row, 'key'));
    }

    public function groupSources() {
        return [
            'No groups' => [null, []],
            'Attribute name' => ['A', ['first']],
            'Numeric array index' => [1, [7]],
            'Closure' => [function($row) {
                    return $row[1];
                }, [7]],
            'Closure gets rowKey' => [function($row, $rowKey) {
                    return $rowKey . $row[0];
                }, ['key5']],
            'Closure with params' => [function($row, $rowKey, $dimID, $start, $length) {
                    return substr($rowKey, $start, $length);
                }, ['ey'], 1, 2],
        ];
    }

    /**
     * @dataProvider calcSources
     */
    public function testAddValues($source, $expected, ...$params) {
        $this->mySetUp();
        $mp = Factory::properties();
        $total = new Collector();
        if ($source !== null) {
            $total->addItem(Factory::calculator($mp, 1, Report::XS), 't1');
            $this->stack->addCalcItem('t1', $source, $params);
        }
        $row = ['A' => 'first', 'B' => 3, 5, 7];
        $this->stack->addValues($row, 'key', $total->items);
        $this->assertSame($expected, $total->sum());
    }

    public function calcSources() {
        require_once ('Testclass.php');
        return [
            'No calcs defined' => [null, 0],
            'Attribute name' => ['B', 3],
            'Numeric array index' => [1, 7],
            'Closure' => [function($row) {
                    return $row[0];
                }, 5],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    return $row[0] * $params;
                }, 50, 10],
            'Get from static method' => [['getStaticValueFromArray'], 3, ['attr' => 'B']],
            'Get from classMethod' => [['Testclass', 'getStaticValueFromArray'], 5, ['attr' => 0]],
            'Get from object method' => [[new Testclass(), 'getValueFromArray'], 7, ['attr' => 1]],
        ];
    }

    /**
     * @dataProvider sheetSources
     */
    public function testAddValuesForSheet($source, $expected, ...$params) {
        $this->mySetUp();
        $mp = Factory::properties();
        $total = new Collector();
        $total->addItem(Factory::sheet($mp, 1, Report::XS), 't1');
        $this->stack->addSheetItem('t1', $source, $params);
        $row = ['A' => 'first', 'B' => 3, 5, 7];
        $this->stack->addValues($row, 'key', $total->items);
        $this->assertSame($expected, $total->t1->sum(null, true));
    }

    public function sheetSources() {
        return [
            'Attribute names' => [['A', 'B'], ['first' => 3]],
            'Numeric array index' => [[0, 1], [5 => 7]],
            'Closure' => [function($row) {
                    return [$row['B'] => $row [0]];
                }, [3 => 5]],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    return [$row['B'] => $row[0] * $params];
                }, [3 => 50], 10],
        ];
    }

    /**
     * @dataProvider dataSources
     */
    public function testGetDimData($source, $expected, ...$params) {
        $dim = new Dimension(1, 'array', $source, 'Testclass', ...$params);
        $this->stack = $dim->dataHandler;
        $mp = Factory::properties();
        $total = new Collector();
        $row = ['A' => 'first', 'B' => [3, 5], [7, 11]];
        $this->assertSame($expected, $this->stack->getDimData($row, 'key'));
    }

    public function dataSources() {
        require_once ('Testclass.php');
        return [
            'Attribute name' => ['B', [3, 5]],
            'Numeric array index' => [0, [7, 11]],
            'Data does not exist' => [3, null],
            'Closure' => [function($row) {
                    return $row[0];
                }, [7, 11]],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    foreach ($row[0] as $data) {
                        $result [] = $data * $params;
                    }
                    return $result;
                }, [14, 22], 2],
            'Get from static method' => [['getStaticValueFromArray'], [3, 5], ['attr' => 'B']],
            'Get from classMethod' => [['Testclass', 'getStaticValueFromArray'], [7, 11], ['attr' => 0]],
            'Get from object method' => [[new Testclass(), 'getValueFromArray'], [7, 11], ['attr' => 0]],
        ];
    }
    
     /**
     * @dataProvider callables
     */
    public function testInvalidAddGroup($source) {
        $this->mySetUp();
        $this->expectExceptionMessage('Callables are not valid for group values.');
        $this->stack->addGroup($source, '');
    }

    public function callables() {
        return [
            'Method in target class' => [['getStaticValueFromArray']],
            'Static method in class' => [['Testclass', 'getStaticValueFromArray']]
        ];
    }

}
