<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */

use gpoehl\phpReport\Collector;
use gpoehl\phpReport\Dimension;
use gpoehl\phpReport\Factory;
use PHPUnit\Framework\TestCase;

class DimensionTest extends TestCase {

    public Collector $total;
    public Dimension $dim;

    public function setUp(): void {
        $this->total = Factory::collector();
    }

    /**
     * @dataProvider sourceProvider
     */
    public function testID_and_LastDim($id, $lastLevel) {
        $dim = new Dimension($id, $lastLevel, null, $this->total);
        $this->assertSame($id, $dim->id);
        $this->assertSame($lastLevel, $dim->lastLevel);
        $this->assertSame(True, $dim->isLastDim);
    }

    public function sourceProvider() {
        return [
            [0, 2],
            [1, 4],
            [3, 5],
        ];
    }

//    public function testID_and_LastDim($id, $source, $exID, $NextID, $isLastDim) {
//        $dim = new Dimension($id, 'array', 'mySource', 'Targetclass', ':noData', ':row detail', ':no group change', 'p1', 'p2');
//        $this->assertInstanceOf(ArrayDataHandler::class, $dim->dataHandler);
//        $this->assertSame(1, $dim->id);
//        $this->assertSame(2, $dim->nextID);
//    }
}
