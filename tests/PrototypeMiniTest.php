<?php

declare(strict_types=1);

/**
 * Unit test of PrototypeMini class
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use gpoehl\phpReport\PrototypeMini;
use gpoehl\phpReport\RuntimeOption;

class PrototypeMiniTest extends TestCase {
    


    #[DataProvider('detailPrintProvider')]
    public function testDimDetailParameter($option, $expected): void {
        $rep = new gpoehl\phpReport\Report($this, ['prototype' => PrototypeMini::class]);
        $rep->setRuntimeOption(RuntimeOption::PrototypeAll);
        $rep->prototype->printArguments = $option;
        $rep->run(['row1' => ['A1', 'B1', 'C' => [1, 2, 3]]]);
        $this->assertSame("start, headerTotal, $expected, footerTotal, finish, ", $rep->out->get());
    }

    public static function detailPrintProvider(): array {
        return [
            'No print' => [false, 'detail'],
            'Print params' => [true, 'detail[{"0":"A1","1":"B1","C":[1,2,3]},"row1",0]'],
        ];
    }
}
