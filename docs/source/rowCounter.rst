Row counter
===========

|project_name| always counts all read data rows.

For each data dimension one CalculatorXS object will be instantiated as an item
within an collector named **rc**.

Using the same objects for incrementing counters as calculating any values provides
you the same methods for accessing the results. This includes also that row 
counters are available at each group level. 

.. note:: Only the last data diminsion has counters for all group levels.
          First data dimensions serve counters down to the group(s) declared
          in it.


.. code-block:: 

    $this->rep = new Report();
    //
    // Some code here ...
    //
    $this->rep->rc->items[*dimensionID*]->sum(*level*).

Example:
.. code-block:: php

    $this->rep = new Report();
    $rc = $this->rep->rc;
    // sum of rows read in all data dimensions at the current group level
    $rc->sum();
    // sum of rows read in all data dimensions at group level = 2
    $rc->sum(2);
    // sum of rows read in data dimensions 0 at current group level
    $rep->rc->{0}->sum();
    $rep->rc->items[0]->sum();     // That's the same
    // sum of rows read in data dimensions 1 at group level = 2
    $rep->rc->{1}->sum(2);
    $rep->rc->items[1]->sum(2);     // That's the same
 

