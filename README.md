# phpReport


Backbone for reports and all other batch like applications.  

phpReport is a modern PHP library designed to handle all tasks related to group 
changes and to provide aggregate functions on each group level.

Documentation can be viewed at https://phpreport.readthedocs.io/en/latest/

Whenever values of declared groups aren't equal between two consecutive rows
assigned actions will be executed. Actions are usually methods to be 
called in the application class(e.g. groupHeader and groupFooter methods). Actions
are the right place to build your output or to do whatever needs to done. 

Other values can be declared to provide aggregate functions like sum, min, max. They
may optionally also aggregated in a sheet. All aggregate funcions as well as a 
lot of different build in counters are available at any time for each group level. 

One of the main advantages of phpReport is the seemless integration into your current
environment which also includes all frameworks. Without any configuration you can
use all your classes, objects and data models. 

Even data retrieval is completely under your control. phpReport accepts any
kind of data and is able to combine data from different sources. Combination means 
that you can join any data sources or work with multi-dimensional arrays. Group changes
and aggregate functions works over all data combinations (we call it dimensions).

Feeding data to phpReport can be separated from the actual application. This means, for example, 
that a controller or a data access class can switch from lazy to eager loading 
or from getting all data at once to reading them in batches to minimize memory. 
The application will not notice this change.  

Requirements
============

The only requirement for **phpReport** is PHP 7.3.0 or later. 

Installation
============

Official installation method is via composer and its packagist package [gpoehl/phpReport](https://packagist.org/packages/gpoehl/phpReport).

```
$ composer require gpoehl/phpReport
```


The same is true for the desired output of your task. You can write data to a database table, a file (eg. excel file), an HTML string, generate a pdf document or send emails. Of course you can do it all together.  


Support us
==========

Consider supporting development of phpReport with a donation of any value. The
sponsor button can be found in the menu bar. 

Usage
=====

The following example shows a basic program structure with two declared groups
and two values to be calculated.

```php
<?php

    require_once __DIR__ . '/vendor/autoload.php';

    use gpoehl\phpreport\Report;

    class MyFirstReport {

        public $rep;
        
        /**
        * It might also be a good idea to instantiate the Report in your
        * controller and feed $rep with the desired data.
        /*
        public function __construct(){
            $data = getDataFromAnyRessource();
            $this->rep = (new Report($this)) 
            ->data('object')
            ->group('customer')         
            ->group('invoice', 'invoiceID')
            ->aggregate('sales', fn($row) => $row->amount * $row->price)
            ->run($data);
            echo $this->rep->output;
        }

        public function init(){
            return "<h1>My very first report</h1>";
        } 

        public function customerHeader($customer, $row){
            return "<h2>Customer $customer</h2>" . $customer->address;
        } 

        public function invoiceHeader($invoice, $row){
            return "<h3>Invoice ID = $invoice</h3>";
        } 

        // Will be called for each data row
        public function detail($row, $rowKey){
            return "<br>$row->item: $row->description";
        } 

        public function invoiceFooter($invoice, $row){
            return "Total sales for invoice $invoice = " . $this->rep->total->sales->sum();
        } 

        public function customerFooter($customer, $row){
            return "Total sales for customer $customer = " . $this->rep->total->sales->sum();
        }

        public function totalFooter(){
            return 
                "Total sales = $this->rep->total->sales->sum()" .
                "Total number of customers: $this->rep->gc->customer->sum()" .
                "Total number of invoices:  $this->rep->gc->invoice->sum()" .
                "Total number of rows:  $this->rep->rc->sum()" ;
        } 
   }   
```

Prototyping
===========
Before developing any action methods have a look at the prototyping options. Prototype
generates a default report showing a lot of interesting stuff. 


```php
    $this->rep = (new Report($this))
    ->setCallOption(Report::CALL_PROTOTYPE);
```


Setup & Configuration
=====================

phpReport is designed to be very flexible. Check the config.php file and make
desired changes. Adapt action method names to follow your own organisation rules. 



Unit Testing
============

Unit testing for phpReport is done using [PHPUnit](https://phpunit.de/).

To execute tests, run `vendor\bin\phpunit` from the command line while in the phpReport root directory.