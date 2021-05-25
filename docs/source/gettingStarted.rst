Getting started
===============

Installation
------------

Official installation method is via composer and its packagist package [gpoehl/phpReport](https://packagist.org/packages/gpoehl/phpReport).

```
$ composer require gpoehl/phpReport
```



After instantiation of the report class call the data() method to specifiy which
the data handler to be used. The data handler is responsible to return values from a data row.
Out of the box there are an ArrayDataHandler and an ObjectDataHandler. You can
also use the 'array' or 'object' aliases. 

Then call the group() method for each group that will be controlled. 

Use the aggregate() method to get aggregate functions for an field / attribute. The
sheet() method organizes these in an horizontal way like in a spreadsheet.

Then pass your data to the run() method.

At certain events defined actions will be executed. As you can imagine thats the
place to do whatever needs to be done.

.. note::
   You have full access to all your existing models. No matter if these are
   data models or models implementing your business rules. 
    

A report which controls two groups and summarizes totals may look like
this example.

.. code-block:: php

    use gpoehl\phpreport\Report;

    require_once(__DIR__ . '/../vendor/autoload.php');
   
    class MyFirstReport {

        // The report object. Use $rep->output to render the output. 
        public $rep;
        
        // @param $data Data usually read by controller object. 
        public function __construct(iterable | null $data){
            $this->rep = (new Report($this)) 
            ->group('customer')         
            ->group('invoice', 'invoiceID')
            ->compute('sales', fn($row) => $row->amount * $row->price);
            ->run($data);
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
            return "<br>$rowKey $row->item: $row->description";
        } 

        public function invoiceFooter($invoice, $row){
            return "Total sales for invoice $invoice = " . $this->rep->total->sales->sum();
        } 

        public function customerFooter($customer, $row){
            return "Total sales for customer $customer = " . $this->rep->total->sales->sum();
        }

        public function totalFooter(){
            return 
                "<br>Total sales: " . $this->rep->total->sales->sum() .
                "<br>Total number of customers: " . $this->rep->gc->customer->sum() .
                "<br>Total number of invoices: ". $this->rep->gc->invoice->sum() .
                "<br>Total number of rows: " . $this->rep->rc->sum();
        } 
   }   
   

 

 

Main features are:

Data handling
  In the most simple form you will call the run method and pass your dataset to this 
  method. phpReport will the iterate over this dataset and execute certain actions.

  It is not required to build a dataset upfront. You can optionally call the run
  method without any data and call the next method once for each data row.
  This might save a lot of memory and processing time.

  phpReport is also able to handle multi-dimensional arrays. Calling the data method
  tells which element contains the sub-array. phpReport will then iterate of the
  sub-array. Sub-array can also have elements where you want specific actions when
  the value changes. So call the group method after the data method to declare
  this element. Same is true for values to be aggregated.

  phpReport might also getting related data to a given row. See data section for
  details.
  Out of the box phpReport offers row counters.

Aggregating values    
  With phpReport it's easy to aggregate values. While calling the aggregate method
  your values are cumulated. Your might also let phpReport count how often you got
  a not null or not zero value as well as figure out the min and max value.

Sheets
  Sheets are a very powerful to aggregate values horizontally. Assume 
  you want to present your calculated data in a table grouped by month. All you need
  to do is calling the sheet method and tell where to find the key (month) and
  where to find the value.

Group changes
  phpReport monitors as much groups as you like. As soon as a value changes phpReport
  executes certain actions like calling group header and group footer methods.
  See actions section for more details.
  To let phpReport know which attributes or elements should be monitored call the
  group method once for for each group. 
  Out of the box phpReport offers group counters which lets you know how often
  a certain value (or group) occurs in an other group.

Prototyping
  Beginners and experienced users of phpReport can benefit from the prototype system.
  Prototying lets you know which method would habe been called, what data row triggered
  the actions, what are the values of the group fields and the values of aggregated
  fields.
  [Prototyping](prototype.rst)
 





