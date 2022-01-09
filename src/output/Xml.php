<?php

declare(strict_types=1);
/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

namespace gpoehl\phpReport\output;


/**
 * This class is not yet read to use
 */

class Xml
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
