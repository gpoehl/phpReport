<?php

declare(strict_types=1);

/**
 * Unit test of Report class. Handling of multiple data dimensions
 */
use gpoehl\phpReport\Report;
use gpoehl\phpReport\PrototypeMini;
use gpoehl\phpReport\RuntimeOption;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportMultipleDimensionTest extends TestCase {

    #[DataProvider('noDataInDimProvider')]
    public function testNoDataInDimension($data): void {
        $out = (new Report($this, ['prototype' => PrototypeMini::class]))
                ->setRuntimeOption(RuntimeOption::Prototype)
                ->group('a', 'firstGroup')
                ->join('b')
                ->run([['firstGroup' => 'A', 'b' => $data]]);
        $this->assertSame('start, headerTotal, beforeA, headerA, detailRoot, noDataRoot, footerA, afterA, footerTotal, finish, ', $out);
    }

    public static function noDataInDimProvider(): array {
        return [
            'Data is Null' => [null],
            'Data is empty Array' => [[]],
        ];
    }

    public function testDimDetailParameter(): void {
        $rep = new Report($this, ['prototype' => PrototypeMini::class]);
        $rep->setRuntimeOption(RuntimeOption::Prototype)
                ->join('B')
                ->run(null, false);
        $this->assertSame('start, headerTotal, ', $rep->out->get());
        $rep->out->delete();
        $rep->next(['A', 'B' => [[1, 2, 3]]]);
        $this->assertSame('detailRoot, detail', substr($rep->out->get(), 0, -2));
    }

    public function testNoGroupChangeParameter(): void {
        $rep = new Report($this, ['prototype' => PrototypeMini::class]);
        $rep->setRuntimeOption(RuntimeOption::Prototype)
                ->group('group1', 0)
                ->join('join1', 'C', ['DimNoGroupChange' => "Row in dim '%s' didn't trigger a group change, "])
                ->run(null, false);
        // First row. Assertion in not really necessary.
        $rep->next(['A', 'B', 'C' => [[1, 3]]]);
        $this->assertSame('start, headerTotal, beforeGroup1, headerGroup1, detailRoot, detail, ', $rep->out->get());
        // Clear output and test second row which didn't trigger a group change.
        $rep->out->delete();
        $rep->next(['A', 'X', 'C' => [[4, 5]]]);
        $this->assertSame("Row in dim 'root' didn't trigger a group change, detailRoot, detail", substr($rep->out->get(), 0, -2));
    }

    public function testNextDimOnObjectProperty(): void {
        $dimrows = [(object) ['D' => 11, 'E' => '1a'], (object) ['D' => 11, 'E' => '1b']];
        $row = (object) ['A' => 10, 'B' => $dimrows];
        $rep = new Report($this, ['prototype' => PrototypeMini::class]);
        $out = $rep->setRuntimeOption(RuntimeOption::Prototype)
                ->join('B')
                ->run([$row]);
        $this->assertSame('start, headerTotal, detailRoot, detail, detail, footerTotal, finish, ', $out);
    }

    public function testSameDataDoesNotTriggerGroupChange(): void {
        $rep = new Report($this, ['prototype' => PrototypeMini::class]);
        $rep->setRuntimeOption(RuntimeOption::Prototype)
                ->group('g1', 'A')
                ->group('g2', 'B')
                // Don't throw error.
                ->join('C', null, ['DimNoGroupChange' => 'noGroupChange%S'])
                ->group('g3', 'D')
                ->run(null, false);
        $out = explode(', ', substr($rep->out->get(), 0, -2));
        $this->assertSame(2, count($out));
        $this->assertSame('start', $out[0]);
        $this->assertSame('headerTotal', $out[1]);

        $rep->out->delete();
        $dimrows = [['D' => 11, 'E' => '1a'], ['D' => 11, 'E' => '1b'], ['D' => 12, 'E' => '2a']];
        $row = ['A' => 10, 'B' => 20, 'C' => $dimrows];
        $rowKey = 'R1';
        $rep->prototype->printArguments = true;
        $rep->next($row, $rowKey);
        $out = explode(', ', substr($rep->out->pop(), 0, -2));

        $this->assertSame('beforeG1' . json_encode([10, $row, $rowKey, 1]), $out[0]);
        $this->assertSame('headerG1' . json_encode([10, $row, $rowKey, 1]), $out[1]);

        $this->assertSame('beforeG2' . json_encode([20, $row, $rowKey, 2]), $out[2]);
        $this->assertSame('headerG2' . json_encode([20, $row, $rowKey, 2]), $out[3]);

        $this->assertSame('detailRoot' . json_encode([$row, $rowKey, 0]), $out[4]);

        $this->assertSame('beforeG3' . json_encode([11, $dimrows[0], 0, 3]), $out[5]);
        $this->assertSame('headerG3' . json_encode([11, $dimrows[0], 0, 3]), $out[6]);

        $this->assertSame('headerDetail' . json_encode([$dimrows[0], 0, 3]), $out[7]);
        $this->assertSame('detail' . json_encode([$dimrows[0], 0, 3]), $out[8]);
        $this->assertSame('detail' . json_encode([$dimrows[1], 1, 3]), $out[9]);
        $this->assertSame('footerDetail' . json_encode([$dimrows[1], 1, 3]), $out[10]);

        $this->assertSame('footerG3' . json_encode([11, $dimrows[1], 1, 3]), $out[11]);
        $this->assertSame('afterG3' . json_encode([11, $dimrows[1], 1, 3]), $out[12]);

        $this->assertSame('beforeG3' . json_encode([12, $dimrows[2], 2, 3]), $out[13]);
        $this->assertSame('headerG3' . json_encode([12, $dimrows[2], 2, 3]), $out[14]);

        $this->assertSame('headerDetail' . json_encode([$dimrows[2], 2, 3]), $out[15]);
        $this->assertSame('detail' . json_encode([$dimrows[2], 2, 3]), $out[16]);

        // no group change at dim 0 but same data in dim 1.
        $rowKey = 'R2';
        $rep->next($row, 'R2');
        $out = explode(', ', substr($rep->out->pop(), 0, -2));
        $this->assertSame('noGroupChangeRoot' . json_encode([$row, $rowKey, 0]), $out[0]);
        $this->assertSame('detailRoot' . json_encode([$row, $rowKey, 0]), $out[1]);
        $this->assertSame('footerDetail' . json_encode([$dimrows[2], 2, 3]), $out[2]);
        $this->assertSame('footerG3' . json_encode([12, $dimrows[2], 2, 3]), $out[3]);
        $this->assertSame('afterG3' . json_encode([12, $dimrows[2], 2, 3]), $out[4]);
        $this->assertSame('beforeG3' . json_encode([11, $dimrows[0], 0, 3]), $out[5]);
        $this->assertSame('headerG3' . json_encode([11, $dimrows[0], 0, 3]), $out[6]);
        $this->assertSame('headerDetail' . json_encode([$dimrows[0], 0, 3]), $out[7]);
        $this->assertSame('detail' . json_encode([$dimrows[0], 0, 3]), $out[8]);
        $this->assertSame('detail' . json_encode([$dimrows[1], 1, 3]), $out[9]);
        $this->assertSame('footerDetail' . json_encode([$dimrows[1], 1, 3]), $out[10]);
        $this->assertSame('footerG3' . json_encode([11, $dimrows[1], 1, 3]), $out[11]);
        $this->assertSame('afterG3' . json_encode([11, $dimrows[1], 1, 3]), $out[12]);
        $this->assertSame('beforeG3' . json_encode([12, $dimrows[2], 2, 3]), $out[13]);
        $this->assertSame('headerG3' . json_encode([12, $dimrows[2], 2, 3]), $out[14]);
        $this->assertSame('headerDetail' . json_encode([$dimrows[2], 2, 3]), $out[15]);
        $this->assertSame('detail' . json_encode([$dimrows[2], 2, 3]), $out[16]);

        $rep->end();
        $out = explode(', ', substr($rep->out->pop(), 0, -2));
        $this->assertSame('footerDetail' . json_encode([$dimrows[2], 2, 3]), $out[0]);
        $this->assertSame('footerG3' . json_encode([12, $dimrows[2], 2, 3]), $out[1]);
        $this->assertSame('afterG3' . json_encode([12, $dimrows[2], 2, 3]), $out[2]);
        $this->assertSame('footerG2' . json_encode([20, $row, $rowKey, 2]), $out[3]);
        $this->assertSame('afterG2' . json_encode([20, $row, $rowKey, 2]), $out[4]);
        $this->assertSame('footerG1' . json_encode([10, $row, $rowKey, 1]), $out[5]);
        $this->assertSame('afterG1' . json_encode([10, $row, $rowKey, 1]), $out[6]);
        $this->assertSame('footerTotal', $out[7]);
        $this->assertSame('finish', $out[8]);
    }
}
