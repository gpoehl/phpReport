<?php

declare(strict_types=1);

/**
 * Description of GroupTest
 *
 * @author Guenter
 */
use gpoehl\backbone\Backbone;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase {

//    public function testRunWithoutData() {
//        $t1 = new BB1();
//        $bb = new Backbone($t1);
//        $bb->setCaller(Backbone::CALL_ALWAYS);
//        $bb->run(null);
//        $this->assertSame('init, header, ' . $bb->noData . 'footer, finalize, ', $bb->output);
//        $bb1 = new Backbone($t1, ['noData' => 'sorry, no data']);
//        $bb1->run(null);
//        $this->assertContains('sorry, no data', $bb1->output);
//    }
//
//    // Check which methods (by groupIndex = level) will be called
//    public function testFlowByGroupIndex() {
//        $t1 = new BB1();
//        $bb = new Backbone($t1, ['buildMethodsByGroupName' => false]);
//        $bb->setCaller(Backbone::CALL_ALWAYS);
//        $bb->next(['G1A', 'G2A', 1, 2]);
//        $this->assertSame('init, header, header_1, header_2, detail, ', $bb->output);
//        $bb->output = null;
//        $bb->next(['G1A', 'G2B', 1, 2]);
//        $this->assertSame('footer_2, header_2, detail, ', $bb->output);
//        $bb->output = null;
//        $bb->next(['G1A', 'G2B', 1, 2]);
//        $this->assertSame('detail, ', $bb->output);
//        $bb->output = null;
//        $bb->next(['G1B', 'G2A', 1, 2]);
//        $this->assertSame('footer_2, footer_1, header_1, header_2, detail, ', $bb->output);
//        $bb->output = null;
//        $bb->end();
//        $this->assertSame('footer_2, footer_1, footer, finalize, ', $bb->output);
//    }
//
//    // Check which methods (by groupName) will be called
//    public function testFlowByGroupName() {
//        $t1 = new BB1();
//        $bb = new Backbone($t1);
//        $bb->setCaller(Backbone::CALL_ALWAYS);
//        $bb->next(['G1A', 'G2A', 1, 2]);
//        $this->assertSame('init, header, header_G1, header_g2, detail, ', $bb->output);
//        $bb->output = null;
//        $bb->end();
//        $this->assertSame('footer_g2, footer_G1, footer, finalize, ', $bb->output);
//    }
//
//    // Verify that ucfirst is also working
//    public function testFlowByGroupNameAndUcfirst() {
//        $t1 = new BB1();
//        $bb = new Backbone($t1, ['ucfirstGroupName' => true]);
//        $bb->setCaller(Backbone::CALL_ALWAYS);
//        $bb->next(['G1A', 'G2A', 1, 2]);
//        $this->assertSame('init, header, header_G1, header_G2, detail, ', $bb->output);
//        $bb->output = null;
//        $bb->end();
//        $this->assertSame('footer_G2, footer_G1, footer, finalize, ', $bb->output);
//    }
//
//    public function testWithoutDefinedGroups() {
//        $t1 = new BB1NoGroups();
//        $bb = new Backbone($t1);
//        $bb->run([
//            ['G1-1', 'G2-1', 1, 0, null]
//        ]);
//        $this->assertEmpty($bb->output);
//    }
//    public function testOneDimension() {
//        $t1 = new BB1();
//        $bb = new Backbone($t1);
//        $bb->next(['G1-1', 'G2-1', 1, 2, 0, 'other']);
//        $this->assertSame(1, $bb->rowCount());
//        $this->assertSame(1, $bb->sum('A'));
//        $this->assertSame(2, $bb->sum('B'));
//        $bb->next(['G1-1', 'G2-1', 7, 2, 0, 'other']);
//        $this->assertSame(8, $bb->sum('A'));
//        // group change
//        $bb->next(['G1-2', 'G2-1', 3, 2, 0, 'other']);
//        $this->assertSame(3, $bb->sum('A'));
//        $this->assertSame(6, $bb->sum('B', 0));  // grand total
//        $bb->end();
//        $this->assertSame(3, $bb->rowCount());
//        $this->assertSame(11, $bb->sum('A'));
//    }
    public function testTwoDimensions() {
        $t2 = new BB2();
        $bb = new Backbone($t2);
        $bb->next(['G1-1', 'G2-1', 'other', 2, 4, [['G3-1', 2, 4], ['G3-1', 3, 6], ['G3-1', 4, 7]]]);
        $this->assertSame(3, $bb->rowCount());
//        $this->assertSame(1, $bb->rowCount(1));
//        $this->assertSame(4, $bb->rowCount(0, [], false));
//        $this->assertSame(2, $bb->sum('A'));
        $this->assertSame(9, $bb->sum('C'));
        $bb->next(['G1-1', 'G2-2', 'other', 3, 5, [['G3-1', 3, 5], ['G3-1', 2, 1], ['G3-1', 4, 8]]]);
        $this->assertSame(9, $bb->sum('B',1));
        // group change
        $bb->next(['G1-2', 'G2-1', 'other', 4, 6, [['G3-1', 6, 7], ['G3-2', 4, 7], ['G3-2', 5, 9]]]);
        $this->assertSame(4, $bb->sum('A', 1));
        $this->assertSame(54, $bb->sum('D', 0));  // grand total
        $bb->end();
        $this->assertSame(3, $bb->rowCount());
        $this->assertSame(9, $bb->sum('A'));
    }

    public function testRunWithoutDataInDim1() {
//        $t1 = new bb2();
//        $bb = new Backbone($t1);
//        $bb->run(
//                [
//                    ['G1A', 'G2A', 1, 2, null],
//                    ['G1A', 'G2B', 2, 3, null],
//                    ['G1B', 'G1A', 2, 3, null],
//                ]
//        );
//        $this->assertStringStartsWith($bb->noData, $bb->output);
    }

    public function testRunWithoutDataInDim2() {
        $bb = new Backbone($this, ['methods'=>['noData' =>':nothing']]);
        $bb->run(null);
        $this->assertEquals('nothing', $bb->output);
    }

    public function fetchValues($row) {
        return ['groupBy' => ['G1' => $row[0], 'G2' => $row[1]]
            , 'calculate' => ['A' => $row[2], 'B' => $row[3]]
        ];
    }

}

/**
 * Basic test class for data sets with one dimension
 */
class BB1 {

    public function __call($name, $arguments) {
        return $name . ', ';
    }

    public function fetchValues($row) {
        return ['groupBy' => ['G1' => $row[0], 'g2' => $row[1]]
            , 'calculate' => ['A' => $row[2], 'B' => $row[3]]
        ];
    }

}

// No groups defined in fetch Values
class BB1NoGroups extends BB1 {

    public function fetchValues($row) {
        return [
            'calculate' => ['A' => $row[2], 'B' => $row[3]]
        ];
    }

}

/**
 * Basic test class for data sets with two dimensions
 */
class BB2 {

    public function fetchValues($row) {
        return ['groupBy' => ['G1' => $row[0], 'G2' => $row[1]]
            , 'calculate' => ['A' => $row[3], 'B' => $row[4]]
            , 'nextDim' =>['data' =>$row[5] ?? null]
        ];
    }

    public function fetchValues_1($row) {
        return ['groupBy' => ['G3' => $row[0]]
            , 'calculate' => ['C' => $row[1], 'D' => $row[2]]
        ];
    }

}

/**
 * Basic test class for data sets with three dimensions
 */
class BB3 {

    public function fetchValues($row) {
        return ['groupBy' => ['G1' => $row[0], 'G2' => $row[1]]
            , 'calculate' => ['A' => $row[2], 'B' => row[3]]
        ];
    }

    public function fetchValues_1($row) {
        return ['groupBy' => ['G1' => $row[0], 'G2' => $row[1]]
            , 'calculate' => ['A' => $row[2], 'B' => row[3]]
        ];
    }

    public function fetchValues_2($row) {
        return ['groupBy' => ['G1' => $row[0], 'G2' => $row[1]]
            , 'calculate' => ['A' => $row[2], 'B' => row[3]]
        ];
    }

}
