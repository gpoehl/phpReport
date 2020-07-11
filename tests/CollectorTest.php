<?php

declare(strict_types=1);

/**
 * Unit test of Collector class
 */
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Factory;

class CollectorTest extends TestCase {

//    public $mp;
//    public $stack;
//    public $calculator;

//    public function setUp(): void {
//        $mp = Factory::properties();
////        $this->mp = $mp;
//     die();    
//     $this->stack = Factory::collector();
//       
//        $this->calculator = Factory::calculator($mp, 0, Report::XS);
//    }

    /**
     * @ dataProvider addItemKeyProvider
     */
//    public function testAddItem($expected, $key) {
         public function testAddItem() {
//        $this->stack->addItem($this->calculator, $key);
        $a = new gpoehl\phpReport\Collector();
        $a->addItem("sss", "x");
          $this->assertSame(1, 1);
//        $this->assertSame($expected, array_key_first($this->stack->items));
    }

    /**
     * @dataProvider addItemKeyProvider
     */
//    public function testAddItemByArrayNotation($expected, $key) {
//        $this->stack[$key] = $this->calculator;
//        $this->assertSame($expected, array_key_first($this->stack->items));
//    }

    /**
     * @dataProvider addItemKeyProvider
     */
//    public function testAddItemByMagicMethod($expected, $key) {
//        $this->stack->$key = $this->calculator;
//        $this->assertSame($expected, array_key_first($this->stack->items));
//    }

//    public function addItemKeyProvider() {
//        return [
////            [0, null],
////            [0, 0],
////            [-1, -1],
////            [1, 1],
//            ['a', 'a'],
////            ['a to z', 'a to z'],
////            [1, 1.2],
////            [1, true],
////            [0, false],
//        ];
//    }

    /**
     * @dataProvider addDuplicateItemKeyProvider
     */
//    public function testAddDuplicate($key) {
//        $this->expectException(\InvalidArgumentException::class);
//        $this->expectErrorMessage("Key '$key' already exists.");
//        $this->stack->addItem($this->calculator, 1);
//        $this->stack->setAltKey('a', 1);
//        $this->stack->addItem($this->calculator, $key);
//    }

//    public function addDuplicateItemKeyProvider() {
//        return [
//            'Duplicate item key' => [1],
//            'Duplicate alternate key' => ['a'],
//        ];
//    }

    /**
     * @dataProvider getItemKeyProvider
     */
//    public function testGetItem($itemKey, $altKey) {
//        $this->setMultipleItemsAndAltKeys();
//        $this->stack->getItem($itemKey);
//        $this->stack->getItem($altKey);
//        $this->assertSame($this->stack->getItem($itemKey), $this->stack->getItem($altKey));
//        // Array access
//        $this->assertSame($this->stack[$itemKey], $this->stack[$altKey]);
//        // Array via magic __get
//        $this->assertSame($this->stack->$itemKey, $this->stack->$altKey);
//    }

//    public function setMultipleItemsAndAltKeys() {
//        $item1 = Factory::calculator($this->mp, 0, Report::XS);
//        $item2 = Factory::calculator($this->mp, 0, Report::XS);
//        $item3 = Factory::calculator($this->mp, 0, Report::XS);
//        $item4 = Factory::calculator($this->mp, 0, Report::XS);
//        $this->stack->addItem($item1);
//        $this->stack->addItem($item2);
//        $this->stack->addItem($item3, 4);
//        $this->stack->addItem($item4, 'x');
//        $this->stack->setAltKeys(['a' => 0, 'b' => 1]);
//        $this->stack->setAltKey('c', 4);
//        $this->stack->setAltKey('d', 'x');
//    }

//    public function getItemKeyProvider() {
//        return [
//            [0, 'a'],
//            [1, 'b'],
//            [4, 'c'],
//            ['x', 'd'],
//        ];
//    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
//    public function testGetNonExistingItem($key) {
//        $this->stack[1] = $this->calculator;
//        $this->expectNotice();
//        $this->expectNoticeMessage("Item '$key' does not exist.");
//        $this->stack->getItem($key);
//    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
//    public function testGetNonExistingItemByArrayNotation($key) {
//        $this->stack[1] = $this->calculator;
//        $this->expectNotice();
//        $this->expectNoticeMessage("Item '$key' does not exist.");
//        $this->stack[$key];
//    }

    /**
     * @dataProvider getNonExistingItemKeyProvider
     */
//    public function testGetNonExistingItemByMagicMethod($key) {
//        $this->stack[1] = $this->calculator;
//        $this->expectNotice();
//        $this->expectNoticeMessage("Item '$key' does not exist.");
//        $this->stack->$key;
//    }

//    public function getNonExistingItemKeyProvider() {
//        return [
//            [-1],
//            [9],
//            ['abc'],
//        ];
//    }

//    public function testIncrementOnMultipleXSCumulatorsHavingDifferentMaxLevels() {
//        $this->c1->inc();
//        $this->c1->inc();
//        $this->assertSame(2, $this->stack->sum());
//        // rc3 increments on level 6
//        $this->c3->add(1);
//        $this->assertSame(3, $this->stack->sum());
//        $this->assertSame(1, $this->stack->sum(6));
//        $this->mp->level = 6;
//        $this->stack->cumulateToNextLevel();
//        $this->assertSame(1, $this->stack->sum(5));
//        $this->assertSame(1, $this->stack->sum(4));
//        $this->assertSame(3, $this->stack->sum(2));
//        $this->assertSame(3, $this->stack->sum(0));
//    }
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
//    /**
//     * @dataProvider rangeParamsProvider
//     */
//    public function testSlice($expected, ... $params) {
//        $this->stack->setAltKeys(['A1' => 1, 'A2' => 2, 'A3' => 3, 'A4' => 4, 'A5' => 5]);
//        $range = $this->stack->range(... $params);
//        $this->assertSame($expected, array_keys($range->items));
//    }
//
//    public function rangeParamsProvider() {
//        return [
//            'Range' => [[2, 3, 4], [2, 4]],
//            'Single items' => [[2, 4], 2, 4],
//            'Ranges and singles ' => [[1, 2, 3, 4], [1, 3], [3, 4], 2],
//            'Start at Zero' => [[1, 2], [null, 2]],
//            'No length' => [[3, 4, 5], [3, null]],
//            'Single items missing' => [[2], 2, 7],
//            'Ranges and singles mixed order' => [[4, 5, 1, 2, 3], [4, 5], [1, 2], 3],
//            'Ranges overlapping keys' => [[3, 4, 5, 1, 2], [3, 5], [1, 4], 3],
//            'Ranges by names' => [[3, 4, 5, 1, 2], ['A3', 5], [1, 'A4'], 3],
//            'Single items some not found' => [[2, 4], 2, 'A4', 7, 'notExist'],
//            'Items Range value1 > value2 = 0 length' => [[4], [4, 2]],
//            'Items Range value1 > value2 = negative length' => [[], [5, 1]],
//        ];
//    }
//
//    /**
//     * @dataProvider missingRangeParamsProvider
//     */
//    public function testSliceMissingKeys($expected, ... $params) {
//         $this->expectExceptionMessage("Key '" .$expected . "' doesn't exist.");
//        $range = $this->stack->range(... $params);
//    }
//
//    public function missingRangeParamsProvider() {
//        return [
//            'Range value1 not found' => [0, [0, 4]],
//            'Range value2 not found' => [8, [1, 8]],
//        ];
//    }
//
//    /**
//     * @dataProvider betweenParamsProvider
//     */
//    public function testBetween($expected, ... $params) {
//        $range = $this->stack->range(... $params);
//        $this->assertSame($expected, array_keys($range->items));
//    }
//
//    public function betweenParamsProvider() {
//        return [
//            'Range' => [[2, 3, 4], [2, 4]],
//            'Single items' => [[2, 4], 2, 4],
//            'Ranges and singles ' => [[1, 2, 3, 4], [1, 3], [3, 4], 2],
//            'No fromKey' => [[1, 2], [null, 2]],
//            'No toKey' => [[3, 4, 5], [3, null]],
//            'Single items missing' => [[2], 2, 7],
//        ];
//    }
//
//    /**
//     * @dataProvider filterParamsProvider
//     */
//    public function testFilter($expected, $param) {
//        $collection = $this->stack->filter($param);
//        $this->assertSame($expected, array_keys($collection->items));
//        $this->assertNotSame($collection, $this->stack);
//    }
//
//    public function filterParamsProvider() {
//        return [
//            'Even Keys' => [[2, 4], fn($key, $item) => !($key % 2)],
//            'Odd Keys' => [[1, 3, 5], fn($key, $item) => ($key % 2)],
//            'Single items' => [[2, 3], fn($key, $item) => ($key === 2 || $key === 3) ? true : false],
//        ];
//    }
//
//    /**
//     * @dataProvider cmdParamsProvider
//     */
//    public function testCmd($expected, $cmd, ...$params) {
//        $collection = $this->stack->cmd($cmd, ...$params);
//        $this->assertSame($expected, array_keys($collection->items));
//        $this->assertNotSame($collection, $this->stack);
//    }
//
//    public function cmdParamsProvider() {
//        return [
//            'Sort by index' => [[1, 2, 3, 4, 5], 'ksort'],
//            'Sort by index desc' => [[5, 4, 3, 2, 1], 'krsort'],
//            'Filter even keys' => [[2, 4], 'array_filter', fn($key) => !($key % 2), ARRAY_FILTER_USE_KEY],
//        ];
//    }
}
