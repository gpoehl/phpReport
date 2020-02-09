
Introduction
============

phpReport simplifies building of reports or batch like applications by handling
of all tasks related to group changes and by calculating desired values. 

phpReport integrates without configuration into any framework. It's usage is 
simple enough to deal with easy tasks and powerful enough to work also with most
complex data structures.

To fetch data from a database or any other source use your own (and well known) 
methods. PhpReport will work with this data no matter if you provide a simple
data set or an multi dimensional array. Getting joined data during data
processing is also possible.

How does it work?
-----------------

After instantiation of the report class you declare which fields holds values
to be compared to detect group changes by calling the group() method.

To declare which values should be cumulated call the calculate() or the sheet()
methods.

Then pass your data to the run() method.

At certain events PhpReport will then call methods in your class where you might
want to prepare your output. 

.. note::
   phpReport integrates fully with your environment and any framework.
   So you have full access to all your business rules implemented in 
   your existing models. 

A simple report might look like

.. code-block:: php

   use gpoehl\phpreport\Report;
   $rep;
   ..
   // $data = get your data from any source
   ..

   // in any method or in a other class (e.g. your controller) 
   $this->rep = (new Report($this)) 
   ->group('customer')
   ->group('invoice', 'invoiceID')
   ->calulate('sales', funcion($row){return $row->amount * $row->price;})
   ->run($data);
   echo $this->rep->output;
   
   public function init(){
       return "<h1>My very first report</h1>";
   } 

   public function customerHeader($customer, $row){
       return "<h2>Customer $customer</h2>" . $customer->address;
   } 

   public function invoiceHeader($invoice, $row){
       return "<h3>Invoidce $invoice</h3>";
   } 

   // Will be called for each data row
   public function detail($row, $rowKey){
       return "<br>$row->item: $row->description";
   } 

   public function invoiceFooter($invoice, $row){
       return "Total of invoice $invoice = " . $this->rep->total->sales->sum();
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
   

 

Prototyping
-----------
  
  Before you start writing any code you might want to use the prototyping system
  to generate a report which shows some data of the currently processed row, 
  names of methods which will be called in real life reports, the value of group
  fields and some values out of the calculated fields.

  It's also a good idea to use prototyping before you start tracing or debugging
  your report. 
  To find out how to use protoyping see :ref:`prototype-label`. 
 


PhpReport has no dependencies. Your class can extend from any class you want.
To avoid naming conflicts you can configure phpReport.
Main features are:

Data handling
  In the most simple form you will call the run method and pass your dataset to this 
  method. phpReport will the iterate over this dataset and execute certain actions.

  It is not required to build a dataset upfront. You can optionally call the run
  method wihout any data and call the next method once for each data row.
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
  Sheets are a very powerful when you need calulated values horizontally. Assume 
  you want to present your calculated data in a table grouped by month. All you need
  to do is calling the sheet method and tell
  phpReport where to find (or how to build) the key (month) and
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
  Prototyint lets you know which method would habe been called, what data row triggered
  the actions, what are the values of the group fields and the values of calculated
  fields.
  [Prototyping](prototype.rst)
 





