
Introduction
============

|project_name| is designed to be the backbone for all applications needing 
control over group changes or when calculation of totals and subtotals is required.
This is usually the case for reports but there are a lot of other use cases.

|project_name| integrates into any framework without configuration. You don't 
have to extend your classes from a |project_name| class. This allows extending
them from any other (framework) class.  

Use of |project_name| is very simple even when you work with very 
complex data structures.  

Data retrieval is done by your own (framework) methods. |project_name| will work with
this data no matter if you provide a data set or an multi dimensional array. 
Joining data, even between different sources, is also easy to accomplish.

Getting started
---------------

After instantiation of the report class call the data() method to specifiy which
the data handler to be used. The data handler is responsible to return values from a data row.
Out of the box there are an ArrayDataHandler and an ObjectDataHandler. You can
also use the 'array' or 'object' aliases. 

Then call the group() method for each group that will be controlled. 

To declare which values should be cumulated call the calculate() or the sheet()
methods.

Then pass your data to the run() method.

At certain events defined actions will be executed. As you can imagine thats the
place to do whatever needs to be done.

.. note::
   You have full access to all your existing models. No matter if these are
   data models or models implementing your business rules. 
    

A report which controls two groups and calculates totals may look like
this example.

.. code-block:: php

   use gpoehl\phpreport\Report;
   $rep;
   ..
   // $data = get your data from any source
   ..

   // It might also be a good idea to instantiate the Report in your controller. 
   $this->rep = (new Report($this)) 
   ->data('object')
   ->group('customer')         
   ->group('invoice', 'invoiceID')
   ->calulate('sales', fn($row) => $row->amount * $row->price)
   ->run($data);
   echo $this->rep->output;
   
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
  this element. Same is true for values to be calculated.

  phpReport might also getting related data to a given row. See data section for
  details.
  Out of the box phpReport calculates row counters.

Calculation values    
  With phpReport it's easy to calculate values. While calling the calculate method
  your values are cumulated. Your might also let phpReport count how often you got
  a not null or not zero value as well as figure out the min and max value.

Sheets
  Sheets are a very powerful to calculate values horizontally. Assume 
  you want to present your calculated data in a table grouped by month. All you need
  to do is calling the sheet method and tell where to find the key (month) and
  where to find the value.

Group changes
  phpReport monitors as much groups as you like. As soon as a value changes phpReport
  executes certain actions like calling group header and group footer methods.
  See actions section for more details.
  To let phpReport know which attributes or elements should be monitored call the
  group method once for for each group. 
  Out of the box phpReport calculates group counters which lets you know how often
  a certain value (or group) occurs in an other group.

Prototyping
  Beginners and experienced users of phpReport can benefit from the prototype system.
  Prototying lets you know which method would habe been called, what data row triggered
  the actions, what are the values of the group fields and the values of calculated
  fields.
  [Prototyping](prototype.rst)
 





