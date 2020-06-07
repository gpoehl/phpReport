<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use gpoehl\phpReport\ArrayDataHandler;
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\ObjectDataHandler;
use PHPUnit\Framework\TestCase;

class DimensionTest extends TestCase {
    
    public function setUp() {
        $total = gpoehl\phpReport\Factory::collector();
        $this->dim = new Dimension(0,0, $this->target, $total);
    }

    /**
     * @dataProvider dataHandler
     */
    public function testDataHandler($handler, $expected) {
        $dim = new Dimension(1, $handler);
        $this->assertInstanceOf($expected, $dim->dataHandler);
    }

    public function dataHandler() {
        return [
            ['array', ArrayDataHandler::class],
            ['object', ObjectDataHandler::class],
            ['ObjecT', ObjectDataHandler::class],
            ['gpoehl\phpReport\ArrayDataHandler', ArrayDataHandler::class],
        ];
    }
    public function testInvalidDataHandler() {
        $this->expectExceptionMessage('DataHandler XXXX does not exist.');
        $dim = new Dimension(1, 'XXXX');
    }
    
    /**
     * @dataProvider sourceProvider
     */
  public function testID_and_LastDim($id, $source, $exID, $nextID, $isLastDim) {
        $dim = new Dimension($id, 'array', $source);
        $this->assertSame($exID, $dim->id);
         $this->assertSame($isLastDim, $dim->isLastDim);
    }
     public function sourceProvider() {
        return [
            [1, null, 1, 2, true],
            [2, 'mySource', 2, 3, false],
        ];
    }
//    public function testID_and_LastDim($id, $source, $exID, $NextID, $isLastDim) {
//        $dim = new Dimension($id, 'array', 'mySource', 'Targetclass', ':noData', ':row detail', ':no group change', 'p1', 'p2');
//        $this->assertInstanceOf(ArrayDataHandler::class, $dim->dataHandler);
//        $this->assertSame(1, $dim->id);
//        $this->assertSame(2, $dim->nextID);
//    }
}
