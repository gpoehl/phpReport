<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport\output;

class Xml extends OutputIF
{

public $xml;
    
    public function __construct(private string $separator = '') {
        $xml = new \XMLWriter();
//        $xml->openMemory();
      $xml->openUri('out.xml');
        $xml->setIndent(true);
        $xml->setIndentString('  ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('xml');
        $this->xml = $xml;
        
    }

     public function writeElement($name, $value){
        $this->xml->writeElement($name, $value);
    }
      
    public function writeHeader($level, $value){
        $this->xml->startElement($level);
        $this->xml->writeElement('header', $value);
    }
     public function writeFooter($value){
       $this->xml->writeElement('footer', $value); 
       $this->xml->endElement();  
    }
      public function writeDetail($value){
        $this->xml->writeElement('detail', $value);
    }
    
    public function end() {
        $this->xml->endDocument();
        return $this->xml->flush();
    }
   

   

}
