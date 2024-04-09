<?php

declare(strict_types=1);

/**
 * Unit test of Report class. Handling of multiple data dimensions
 */
use gpoehl\phpReport\Report;
use gpoehl\phpReport\PrototypeMini;
use gpoehl\phpReport\RuntimeOption;
use PHPUnit\Framework\TestCase;

final class ReportMultipleDimensionRowTest extends TestCase {

    public $row = [
        'A', 'customer' => [
            ['cust1', 'order' => [
                    'order1A' => [
                        'item' => ['item1', 1]
                    ]
                ]
            ],
            ['cust2', 'order' => [
                    'order2A' => [
                        'item' => [
                           98 => ['item1', 1],
                           99 => ['item2', 2],
                        ]
                    ],
                ]
            ]
        ]
    ];

    public function testGetRow(): void {
        $rep = (new Report($this, ['prototype' => PrototypeMini::class]));
        $rep->setRuntimeOption(RuntimeOption::Prototype);
        $rep->join('customer')
                ->join('order')
                ->join('item')
                ->run([$this->row]);
        // Now Dimension holds the last Row and Rowkey 
        $customer = $this->row['customer'][1];
        $order = $customer['order']['order2A'];
        $item = $order['item'][99];
        // Test getRow()
        $this->assertSame($customer, $rep->getRow(1));
        $this->assertSame($customer, $rep->getRow('customer'));
        $this->assertSame($order, $rep->getRow(2));
        $this->assertSame($order, $rep->getRow('order'));
        $this->assertSame($item, $rep->getRow(3));
        $this->assertSame($item, $rep->getRow('item'));
        // Test getRowKey
        $this->assertSame(1, $rep->getRowKey(1));
        $this->assertSame(1, $rep->getRowKey('customer'));
        $this->assertSame('order2A', $rep->getRowKey(2));
        $this->assertSame('order2A', $rep->getRowKey('order'));
        $this->assertSame(99, $rep->getRowKey(3));
        $this->assertSame(99, $rep->getRowKey('item'));

        $this->assertSame('start, headerTotal, detailRoot, detailCustomer, detailOrder, detail, detail, detailCustomer, detailOrder, detail, detail, footerTotal, finish, ', $rep->out->get());
    }
}
