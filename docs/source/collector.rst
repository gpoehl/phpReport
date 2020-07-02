Collector
=========

phpReport has a very powerful feature to aggregate values and increment counters.
Each value and counter is implemented as a calculator object. These objects 
will be assigned to one collector.

phpReport instantiates the following collectors. 

.. note::
    All entities assigned to one of those collectors are cumulated to 
    higher group levels.


Row counter collector
----------------------

The row counter collector is named **rc**. For each data dimension one CalculatorXS
object will be instantiated.

Group counter collector
-----------------------

The group counter collector is named **gc**. For each defined group one CalculatorXS
object will be instantiated.

Total collector
---------------

The total collector is named **total**. The collector is used to hold all calculator
objects instantiated by the aggregate() method and the sheet objects instantiated
by the sheet() method.

You can assign further collectors, sheets or calculators to this total collector
or any other collector in this tree.
So it's possible to build a hirachichal structure of aggregated values.


Accessing values
----------------

There are multiple options to access a calculater object. The long version looks
like

$this->rep->*collectorName*->items[*itemName*].

.. code-block:: php

    $rep = $this->report;
    $rep->t->*name*; 
    $rep->t['*name*'];
    $rep->rc;
    $rep->rc->{0};
    $rep->rc->[0];
 
.. include:: rowCounter.rst
.. include:: groupCounter.rst

Range of values
---------------
To apply aggregate funcions only to a subset of collected items use the range method. 
