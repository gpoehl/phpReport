<?php

declare(strict_types=1);

/**
 * Unit test of ArrayDataHandler class
 */
use gpoehl\phpReport\ObjectDataHandler;
use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class ObjectDataHandlerTest extends TestCase {

    public $stack;
    public $row;

    public function setUp():void{
           $this->row = new Row();
    }
    
    
    /**
     * Setup method must be called by tests when neede.
     * Default SetUp() not used because Dimension must be instantiated with
     * different parameters.
     * @return void
     */
    public function mySetUp(): void {
        $dim = new Dimension(1,'object');
        $this->stack = $dim->dataHandler;
    }

    /**
     * @dataProvider groupSources
     */
    public function testGetGroupValues($source, $expected, ...$params) {
        $this->mySetUp();
        if ($source !== null) {
            $this->stack->addGroup($source, $params);
        }
        $this->assertSame($expected, $this->stack->getGroupValues($this->row, 'key'));
    }

    public function groupSources() {
        return [
            'No groups' => [null, []],
            'Attribute name' => ['a', ['first']],
            'Closure' => [function($row) {
                    return $row->arr[1];
                }, [7]],
            'Closure gets rowKey' => [function($row, $rowKey) {
                    return $rowKey . $row->num5;
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
        $this->stack->addValues($this->row, 'key', $total->items);
        $this->assertSame($expected, $total->sum());
    }

    public function calcSources() {
        require_once ('Testclass.php');
        return [
            'No calcs defined' => [null, 0],
            'Attribute name' => ['num3', 3],
            'Closure' => [function($row) {
                    return $row->num5;
                }, 5],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    return $row->num5 * $params;
                }, 50, 10],
            'Get from row object method' => [['getValue'], 3, 'num3'],
            'Get from classMethod' => [['Testclass', 'getStaticValueFromObject'], 10, 'num5' , 2],
            'Get from other object method' => [[new Testclass(), 'getValueFromObject'], 21, 'num7' ,3],
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
        $this->stack->addValues($this->row, 'key', $total->items);
        $this->assertSame($expected, $total->t1->sum(null, true));
    }

    public function sheetSources() {
        return [
            'Attribute names' => [['a', 'num3'], ['first' => 3]],
            'Closure' => [function($row) {
                    return [$row->a => $row->num5];
                }, ['first' => 5]],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    return [$row->num3 => $row->num5 * $params];
                }, [3 => 50], 10],
        ];
    }

    /**
     * @dataProvider dataSources
     */
    public function testGetDimData($source, $expected, ...$params) {
         $dim = new Dimension(1, 'object', $source, 'Testclass', null, null, null, ...$params);
          $this->stack = $dim->dataHandler;
        $mp = Factory::properties();
        $total = new Collector();
        $this->stack->setNextDataDimSource($source, $params);
        $this->assertSame($expected, $this->stack->getDimData($this->row, 'key'));
    }

    public function dataSources() {
        require_once ('Testclass.php');
        return [
            'Attribute name' => ['arr', [5, 7]],
            'Closure' => [function($row) {
                    return $row->arr;
                }, [5, 7]],
            'Closure with params' => [function($row, $rowKey, $dimID, $params) {
                    foreach ($row->arr as $data) {
                        $result [] = $data * $params;
                    }
                    return $result;
                }, [10, 14], 2],
            'Get from row object method' => [['getValue'], [5, 7], 'arr'],
            'Get from classMethod' => [['Testclass', 'getStaticValueFromObject'], [5, 7], 'arr'],
            'Get from object method' => [[new Testclass(), 'getValueFromObject'], [5, 7], 'arr'],
        ];
    }

}

class Row {

    public $a = 'first';
    public $num3 = 3;
    public $num5 = 5;
    public $num7 = 7;
    public $arr = [5, 7];

    public function getValue($val) {
        return $this->$val;
    }

}
