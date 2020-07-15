<?php

declare(strict_types=1);

/**
 * Unit test of Collector class
 */
use gpoehl\phpReport\Factory;
use gpoehl\phpReport\Report;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase {

    public $stack;
    public $calculator;
    public $mp;

    public function setUp(): void {
        $this->mp = Factory::properties();
        $this->stack = Factory::collector();
        $this->calculator = Factory::calculator($this->mp, 0, Report::XS);
    }

    /**
     * @dataProvider addItemKeyProvider
     * @dataProvider addCastedItemKeyProvider
     */
    public function testAddItem($expected, $key) {
        $this->stack->addItem($this->calculator, $key);
        $this->assertSame($expected, \array_key_first($this->stack->items));
    }

    /**
     * @dataProvider addItemKeyProvider
     * @dataProvider addCastedItemKeyProvider
     */
    public function testAddItemByArrayNotation($expected, $key) {
        $this->stack[$key] = $this->calculator;
        $this->assertSame($expected, \array_key_first($this->stack->items));
    }

    /**
     * @dataProvider addItemKeyProvider 
     * @dataProvider addMagicItemKeyProvider
     */
    public function testAddItemByMagicMethod($expected, $key) {
        $this->stack->$key = $this->calculator;
        $this->assertSame($expected, \array_key_first($this->stack->items));
    }

    public function addItemKeyProvider() {
        return [
            [0, null],
            [0, 0],
            [-1, -1],
            [1, 1],
            ['a', 'a'],
            ['a to z', 'a to z'],
            [1, true],
            [0, false],
        ];
    }

    public function addCastedItemKeyProvider() {
        return [
            [1, 1.2],
        ];
    }

    public function addMagicItemKeyProvider() {
        return [
            ['1.2', 1.2],
        ];
    }

    /**
     * @dataProvider addDuplicateItemKeyProvider
     */
    public function testAddDuplicate($key) {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage("Key '$key' already exists.");
        $this->stack->addItem($this->calculator, 1);
        $this->stack->setAltKey('a', 1);
        $this->stack->addItem($this->calculator, $key);
    }

    public function addDuplicateItemKeyProvider() {
        return [
            'Duplicate item key' => [1],
            'Duplicate alternate key' => ['a'],
        ];
    }

    /**
     * @dataProvider getItemKeyProvider
     */
    public function testGetItem($itemKey, $altKey) {
        $this->setMultipleItemsAndAltKeys();
        $this->stack->getItem($itemKey);
        $this->stack->getItem($altKey);
        $this->assertSame($this->stack->getItem($itemKey), $this->stack->getItem($altKey));
        // Array access
        $this->assertSame($this->stack[$itemKey], $this->stack[$altKey]);
        // Array via magic __get
        $this->assertSame($this->stack->$itemKey, $this->stack->$altKey);
    }

    public function setMultipleItemsAndAltKeys() {
        $item1 = Factory::calculator($this->mp, 0, Report::XS);
        $item2 = Factory::calculator($this->mp, 0, Report::XS);
        $item3 = Factory::calculator($this->mp, 0, Report::XS);
        $item4 = Factory::calculator($this->mp, 0, Report::XS);
        $item5 = Factory::calculator($this->mp, 0, Report::XS);
        $this->stack->addItem($item1);
        $this->stack->addItem($item2);
        $this->stack->addItem($item3);
        $this->stack->addItem($item4, 4);
        $this->stack->addItem($item5, 'x');
        $this->stack->setAltKeys(['a' => 0, 'b' => 1, 'c' => 2]);
        $this->stack->setAltKey('d', 4);
        $this->stack->setAltKey('e', 'x');
    }

    public function getItemKeyProvider() {
        return [
            [0, 'a'],
            [1, 'b'],
            [4, 'd'],
            ['x', 'e'],
        ];
    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
    public function testGetNotExist($key) {
        $this->stack[1] = $this->calculator;
        $this->expectNotice();
        $this->expectNoticeMessage("Item '$key' does not exist.");
        $this->stack->getItem($key);
    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
    public function testGetNotExistByArrayNotation($key) {
        $this->stack[1] = $this->calculator;
        $this->expectNotice();
        $this->expectNoticeMessage("Item '$key' does not exist.");
        $this->stack[$key];
    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
    public function testGetNotExistByMagicMethod($key) {
        $this->stack[1] = $this->calculator;
        $this->expectNotice();
        $this->expectNoticeMessage("Item '$key' does not exist.");
        $this->stack->$key;
    }

    public function getNonExistingItemKeyProvider() {
        return [
            [-1],
            [9],
            ['abc'],
        ];
    }

//    public function testFormularsOnColletorsHavingDifferentTypOfCumulators() {
//        $this->c1->inc();
//        $this->c1->inc();
//        $this->c3->add(2);
//        $this->c3->add(0);
//        $this->c3->add(null);
//        $this->c4->add(5);
//        $this->c4->add(3);
//        $this->assertSame(12, $this->stack->sum(0), "Sum of all items at level 0");
//        $this->assertSame([1 => 2, 0, 2, 8, 0], $this->stack->sum(0, true), "Sum of all items at level 0 as array");
//        $this->assertSame(12, $this->stack->sum(2), "Sum of all items at level 2");
//        $this->assertSame([1 => 2, 0, 2, 8, 0], $this->stack->sum(2, true), "Level 2 as array");
//        $this->assertSame(10, $this->stack->sum(4), "C1 is does not exitst on level 4");
//        $this->assertSame([1 => 0, 0, 2, 8, 0], $this->stack->sum(4, true), "C1 is part of result. All items in stack are in result array");
//    }
//    public function testAdd() {
//        $this->stack->add([1 => 4, 6, 8, 5 => [3 => 1, 6 => 2, 9 => 3]]);
//        $this->assertSame(4, $this->stack->{1}->sum(0), "Sum of item c1");
//        $this->assertSame(6, $this->stack->{2}->sum(0), "Sum of item c2");
//        $this->assertSame(8, $this->stack->{3}->sum(0), "Sum of item c3");
//        $this->assertSame(0, $this->stack->{4}->sum(0), "Sum of item c4");
//        $this->assertSame(6, $this->stack->{5}->sum(0), "Sum of sheet");
//        $this->assertSame(24, $this->stack->sum(), "Sum of c1 to c4 and sheet");
//    }
//    public function testNnFailure() {
//        $this->expectException(Error::class);
//        $this->stack->nn();
//    }
//
//    public function testNzFailure() {
//        $this->expectException(Error::class);
//        $this->stack->nz();
//    }
//
//    public function testMinFailure() {
//        $this->expectException(Error::class);
//        $this->stack->min();
//    }
//
//    public function testMaxFailure() {
//        $this->expectException(Error::class);
//        $this->stack->max();
//    }
//    public function testIterativ() {
//        $multi = Factory::collector();
//        $this->stack->addItem($multi, 'multi');
//        $s1 = Factory::calculator($this->mp, 2, Report::XS);
//        $s2 = Factory::calculator($this->mp, 2, Report::XS);
//        $multi->addItem($s1);
//        $multi->addItem($s2);
//        $s1->add(5);
//        $s2->add(7);
//        $this->c1->inc();
//        $this->c1->inc();
//        $this->c3->add(1);
//        $this->assertSame(12, $multi->sum());
//        $this->assertSame(15, $this->stack->sum(), "Sum incl sub collector");
//        $this->assertSame([5, 7], $multi->range([0, 1])->sum(null, true), "range from collector multi key 1 to 2 as array");
//        $this->assertSame([5, 7], $multi->between([0, 1])->sum(null, true), "between from collector multi key between 0 and 1 as array");
//        $this->assertSame([3 => 1, 0, 0, 'multi' => 12], $this->stack->between([3, 5], 'multi')->sum(null, true), "between from stack collector key between 3 and 6 plus 'multi' as array");
//    }
//

    /**
     * @dataProvider rangeParamsProvider
     */
    public function testRange($expected, ... $params) {
        $this->setMultipleItemsAndAltKeys();
        $this->stack->setAltKeys(['A1' => 1, 'A2' => 2, 'A3' => 3, 'A4' => 4, 'A5' => 5]);
        $collector = $this->stack->range(... $params);
        $this->assertSame($expected, array_keys($collector->items));
    }

    public function rangeParamsProvider() {
        return [
            'Range' => [[0, 1, 2, 4], [0, 4]],
            'Single items' => [[1, 4, 'x'], 1, 4, 'e'],
            'Ranges and singles ' => [[0, 1, 4, 'x', 2], [0, 1], [4, 'x'], 2],
            'Start at Zero' => [[0, 1, 2], [null, 2]],
            'No length' => [[4, 'x'], [4, null]],
            'Ranges and singles mixed order' => [[4, 'x', 0, 1, 2], [4, 'x'], [0, 1], 2],
            'Ranges overlapping keys' => [['x', 2, 4, 1, 0], 'x', [2, 4], [1, 4], [null, 1]],
            'Ranges by alt Keys' => [['x', 2, 4, 1, 0], 'e', ['c', 'd'], ['b', 'd'], [null, 'b']],
            'Items Range value1 > value2 = 0 length' => [[4], [4, 1]],
            'Items Range value1 > value2 = negative length' => [[], ['e', 1]],
        ];
    }

    /**
     * @dataProvider missingRangeParamsProvider
     */
    public function testSliceMissingRangeKeys($expected, ... $params) {
        $this->setMultipleItemsAndAltKeys();
        $this->expectExceptionMessage("Key '$expected' doesn't exist.");
        $range = $this->stack->range(... $params);
    }

    public function missingRangeParamsProvider() {
        return [
            'Range value1 not found' => [3, [3, 4]],
            'Range value2 not found' => [8, [1, 8]],
        ];
    }

    /**
     * @dataProvider missingSingleItemsParamsProvider
     */
    public function testSliceMissingSingleItems($expected, ... $params) {
        $this->setMultipleItemsAndAltKeys();
        $this->expectNotice();
        $this->expectNoticeMessage("Item '$expected' doesn't exist.");
        $range = $this->stack->range(... $params);
    }

    public function missingSingleItemsParamsProvider() {
        return [
            [7, 2, 'd', 7, 'notExist'],
            ['notExist', 2, 'd', 'notExist'],
        ];
    }

    /**
     * @dataProvider betweenParamsProvider
     */
    public function testBetween($expected, ... $params) {
        $this->setMultipleItemsAndAltKeys();
        $collector = $this->stack->between(... $params);
        $this->assertSame($expected, array_keys($collector->items));
    }

    public function betweenParamsProvider() {
        return [
            'FromTo' => [[1, 2, 4], [1, 4]],
            'Single items' => [[2, 4], 2, 4],
            'FromTo and singles ' => [[0, 1, 2, 'x'], [1, 2], [2, 3], 'x', 'a'],
            'No fromKey' => [[0, 1, 2, 'x'], [null, 2]],
            'No toKey' => [[2, 4], [2, null]],
            'Single items missing' => [[2], 2, 7],
            'Compare 0 - very strange php behavior' => [[0, 1, 2, 4, 'x'], [0, null]],
        ];
    }

    /**
     * @dataProvider filterParamsProvider
     */
    public function testFilter($expected, $param) {
        $this->setMultipleItemsAndAltKeys();
        unset($this->stack->items['x']);
        $this->stack[3] = clone $this->calculator;
        $collector = $this->stack->filter($param);
        $this->assertSame($expected, array_keys($collector->items));
    }

    public function filterParamsProvider() {
        return [
            'Even Keys' => [[0, 2, 4], fn($key, $item) => !($key % 2)],
            'Odd Keys' => [[1, 3], fn($key, $item) => ($key % 2)],
            'Single items' => [[0, 2], fn($key, $item) => ($key === 2 || $key === 0) ? true : false],
        ];
    }

    /**
     * @dataProvider cmdParamsProvider
     */
    public function testCmd($expected, $cmd, ...$params) {
        $this->setMultipleItemsAndAltKeys();
        unset($this->stack->items['x']);
        $collector = $this->stack->cmd($cmd, ...$params);
        $this->assertSame($expected, array_keys($collector->items));
    }

    public function cmdParamsProvider() {
        return [
            'Sort by index' => [[0, 1, 2, 4], 'ksort'],
            'Sort by index desc' => [[4, 2, 1, 0], 'krsort'],
            'Filter even keys' => [[0, 2, 4], 'array_filter', fn($key) => !($key % 2), ARRAY_FILTER_USE_KEY],
        ];
    }

}
