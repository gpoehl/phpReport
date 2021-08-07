Default Collectors
==================

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
objects instantiated by the compute() method and the sheet objects instantiated
by the sheet() method.

You can assign further collectors, sheets or calculators to this total collector
or any other collector in this tree.
So it's possible to build a hirachichal structure of aggregated values.

.. include:: rowCounter.rst
.. include:: groupCounter.rst