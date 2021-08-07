Group counter
=============

As soon as you declare groups |project_name| counts how often a group occurs
in the higher level group.
In other words we cout how many different values of a certain group has been processed.

For each group one CalculatorXS object will be instantiated as an item
within an collector named **gc**.

Group counters are, like any other aggregation methods, available at each group level. 

.. attention:: 
    Make sure you access the correct gc item. To get the counter of
    the current group use the item of the previous group.

.. tip:: 
    Use a negative value for the group level. -1 means the level above
    the current level.


.. code-block:: 

    $this->rep = new Report();
    //
    // Some code here ...
    //
    $this->rep->gc->items[*group*]->sum(*level*).

Example:

.. code-block:: 

    $this->rep = new Report();
    $gc = $this->rep->gc;
    // Sum of group in the group above the current group.
    $gc->sum(-1);
    // sum of group  data dimensions at group level = 2
    $rc->sum(2);
    // sum of rows read in data dimensions 0 at current group level
    $rep->rc->{0}->sum();
    $rep->rc->items[0]->sum();     // That's the same
    // sum of rows read in data dimensions 1 at group level = 2
    $rep->rc->{1}->sum(2);
    $rep->rc->items[1]->sum(2);     // That's the same
 

