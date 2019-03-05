<?php

declare(strict_types=1);

/**
 * Unit test of Collector
 */
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use gpoehl\backbone\Factory;

class CollectorTest extends TestCase {

    public $mp;
    protected $stack;
    protected $rc1, $rc2, $rc3;

    public function setUp() {
        $mp = Factory::properties();
        $this->mp = $mp;

        $cumulator = Factory::cumulator($mp, 3, Factory::XL);
        $this->b = Factory::sheet($cumulator);

        $this->c1 = Factory::cumulator($mp, 2, Factory::XS);
        $this->c2 = Factory::cumulator($mp, 4, Factory::XS);
        $this->c3 = Factory::cumulator($mp, 6, Factory::REGULAR);
        $this->c4 = Factory::cumulator($mp, 6, Factory::XL);

        $this->stack = Factory::collector();
        $this->stack->addItem($this->c1, 1);    // start array index at 1
        $this->stack->addItem($this->c2);
        $this->stack->addItem($this->c3);
        $this->stack->addItem($this->c4);
    }


    public function testArrayAccess() {
        $this->stack[2]->add(4);
        $this->assertSame(4, $this->stack[2]->sum());
        $this->stack[] = $this->c2;
        $this->assertSame(4, $this->stack[5]->sum());
    }

    public function testMagicAccess() {
        $this->stack->{2}->add(4);
        $this->assertSame(4, $this->stack->{2}->sum());
        $this->stack->{5} = $this->c2;
        $this->assertSame(4, $this->stack->{5}->sum());
    }

    public function testAccessWithInvalidKeyProducesNotice() {
        $this->expectException(Notice::class);
        $a = $this->stack->getItem('missingKey');
    }

    public function testArrayAccessWithInvalidKeyProducesNotice() {
        $this->expectException(Notice::class);
        $a = $this->stack[99];
    }

    public function testMagicAccessWithInvalidKeyProducesNotice() {
        $this->expectException(Notice::class);
        $a = $this->stack->missingKey;
    }

    public function testIncrementOnMultipleXSCumulatorsHavingDifferentMaxLevels() {
        $this->c1->inc();
        $this->c1->inc();
        $this->assertSame(2, $this->stack->sum());
        // rc3 increments on level 6
        $this->c3->add(1);
        $this->assertSame(3, $this->stack->sum());
        $this->assertSame(1, $this->stack->sum(6));
        $this->mp->level = 6;
        $this->stack->cumulateToNextLevel();
        $this->assertSame(1, $this->stack->sum(5));
        $this->assertSame(1, $this->stack->sum(4));
        $this->assertSame(3, $this->stack->sum(2));
        $this->assertSame(3, $this->stack->sum(0));
    }

    public function testFormularsOnCollorsHavingDifferentTypOfCumulators() {
        $this->c1->inc();
        $this->c1->inc();
        $this->c3->add(2);
        $this->c3->add(0);
        $this->c3->add(null);
        $this->c4->add(5);
         $this->c4->add(3);
        $this->assertSame(12, $this->stack->sum(0), "Sum of all items at level 0");
        $this->assertSame([1 => 2, 0, 2, 8], $this->stack->sum(0, true), "Sum of all items at level 0 as array");
        $this->assertSame(12, $this->stack->sum(2), "Sum of all items at level 2");
        $this->assertSame(4, $this->stack->nn(), "NN level 0. C1 is XS type cumulator and will not increase the counter.");
        $this->assertSame(3, $this->stack->nz(), "Sum of all items at level 0");
        $this->assertSame(3, $this->stack->min(), "Sum of all XL items at level 0 (only values of c4");
        $this->assertSame(5, $this->stack->max(), "Sum of all XL items at level 0");
        $this->assertSame([1 => 2, 0, 2, 8], $this->stack->sum(2, true), "Level 2 as array");
        $this->assertSame(10, $this->stack->sum(4), "C1 is does not exitst on level 4");
        $this->assertSame([1 => 0, 0, 2, 8], $this->stack->sum(4, true), "C1 is part of result. All items in stack are in result array");
    }

    public function testRsum() {
        $this->c1->inc();
        $this->c1->inc();
        $this->c3->add(1);
        $this->assertSame(2, $this->stack->rsum(1, null, 2), "c1 on level 2");
        $this->assertSame(0, $this->stack->rsum(1, null, 3), "c1 on level 3");
        $this->assertSame(0, $this->stack->rsum(2, null, 0), "not existing c2 on level 0");
        $this->assertSame(1, $this->stack->rsum(3, null, 2), "c3 on level 2");
        $this->assertSame([1 => 2, 0, 1], $this->stack->rsum(1, 3, 0, true), "c1 till c3 on level 0 as array");
        $this->assertSame([1 => 0, 0, 1], $this->stack->rsum(1, 3, 3, true), "c1 till c3 on level 3 as array");
        $this->assertSame([1 => 0, 0, 1, 0], $this->stack->sum(3, true), "Sum at level3 as array. sum() uses forEach loop. C2 exists.");
        $this->assertSame([1 => 2, 0], $this->stack->rsum(1, 2, 2, true), "c1 till c2 on level 2 as array");
        $this->assertSame([1 => 2, 0], $this->stack->rsum(1, 2, null, true), "c1 till c2 on level 2 as array");
        $this->assertSame([1 => 2], $this->stack->rsum(1, null, 2, true), "only c1 on level 2 as array");
    }

    public function testIterativ() {
        $multi = Factory::collector();
        $this->stack->addItem($multi,'multi');
        $s1 = Factory::cumulator($this->mp, 2, Factory::XS);
        $s2 = Factory::cumulator($this->mp, 2, Factory::XS);
        $multi->addItem($s1);
        $multi->addItem($s2);
        $s1->add(5);
        $s2->add(7);
        $this->c1->inc();
        $this->c1->inc();
        $this->c3->add(1);
        $this->assertSame(12, $multi->sum());
        $this->assertSame(15, $this->stack->sum(), "Sum incl sub colloctor");
        $this->assertSame([5, 7], $multi->rsum(0, 1, null,  true), "rsum from collector multi key 1 to 2 as array");
        $this->assertSame([5, 7], $this->stack->multi->rsum(0, 1, null,  true), "rsum from collector multi via stack collector key 1 to 2 as array");
    }

}
