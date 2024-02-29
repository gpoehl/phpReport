Group changes
=============

Compexity in applications usually grows exceptionlly with every addidtional group
which needs to be managed.

With |project_name| you only need to call the group() method once for every group.
Groups can be controlled in every data dimension. Just call the 
group() method after calling the related data() method.

A group change occurs when within a data dimension when group values of the previous
row don't equal those of the current row.

.. tip:: Data don't need to be sorted by groups. But make sure that
         rows are grouped by the group field or you might raise 
         unwanted group changes. 

Once a group change has been detected the appropiate action methods (group headers
and group footers) will be executed.

.. php:method:: group ($name, $source = null, $actions = null, ...params):report

    Declare a data group. 

    :param string $name: The name to be used for this group. 
     This name might be used to build method names for group headers and footers 
     and must be unique.
     All group related values (including cumulated values from sum or sheet methods 
     as well as row and group counters) can be retrieved by this group name or the group level.

    :param mixed $source: Source of the group value. Defaults to the name.
     Use the attribute name when data row is an object or the key name when data row is an array.
      It's also possiblbe to use a callable (a closure or an array having 
      class and method parameters) expecting $row and $rowKey as parameters. 

     :param iterable|null $actions: Array of group related actions to replace configurated actions.

    :param mixed $params: Variadic parameters to be passed to callables declared 
     with value parameter. 

    :returns: $this which allows method call chaining.


Example

.. code-block:: php

    $rep = (new Report ($this))
    ->group ('region')
    ->group ('year', fn($row) => substr($row->saleDate, 0, 4))
    ->group ('month', fn($row) => substr($row->saleDate, 5, 2))
    ->group ('customer', 'customerID', null, '</table>')

The above example declares four groups (group level 1 to 4) to be monitored. 
Instead of executing the customer footer action the string '</table>' will be 
appended to the $output property.

.. note::
    Group level 0 is always the top level which is called grandTotal.

For each declared group a group counter will be instantiated and incremented when 
a group change occurs. See 'Calculation' for details.
