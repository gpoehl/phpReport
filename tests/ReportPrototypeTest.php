<?php

declare(strict_types=1);

/**
 * Unit test of Group class
 */
use PHPUnit\Framework\TestCase;
use gpoehl\phpReport\Report;
use gpoehl\phpReport\Prototype;
use gpoehl\phpReport\PrototypeMini;
use gpoehl\phpReport\RuntimeOption;

class ReportPrototypeTest extends TestCase {

    public Report $rep;
    
   public function testDefaultClass(){
       $rep = new Report($this);
       $this->assertSame($rep->prototype, Null);
       $rep->setRuntimeOption(RuntimeOption::Prototype);
       $this->assertInstanceOf(Prototype::class, $rep->prototype);
   }
   
   public function testInitializeReportWithParam(){
       $rep = new Report($this, ['prototype' => PrototypeMini::class]);
       $this->assertSame($rep->prototype, Null);
       $rep->setRuntimeOption(RuntimeOption::Prototype);
       $this->assertInstanceOf(PrototypeMini::class, $rep->prototype);
   }
   
    public function testCallPrototypeFunctionFromTarget(){
       $this->rep = new Report($this, ['prototype' => PrototypeMini::class]);
       $this->rep->run(['a']);
       $this->assertInstanceOf(PrototypeMini::class, $this->rep->prototype);
       $this->assertSame('start', substr($this->rep->out->get(), 0, -2));
   }
   
   public function start(){
       return $this->rep->prototype();
   }
    
}
