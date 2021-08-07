.. _prototype-label:

Prototyping
===========

Before you start writing any code you might want to use the prototyping system
to generate a report which shows some data of the currently processed row, 
names of methods which will be called in real life applications, the value of group
fields and some values out of the aggregated fields.

Prototype tells also what the real action would be (e.g. Call method xy or 
append string 'foobarbaz').

It's also a good idea to use prototyping before you start tracing or debugging
your application. 

You can call the prototype function at any time by just calling

.. code-block:: php

   $rep->prototype();

This will return an html table for the current action key.


The other way is setting the call method parameter by calling the 
setCallAction() method with one of the following constants as parameter.

:CALL_EXISTING = 0:  Call methods in owner class only when implemented. Default.
:CALL_ALWAYS = 1:  Call also not existing methods in owner class. 
   This allows using magic function calls.
:CALL_PROTOTYPE = 2:  Call methods in prototype class when they are not 
   implemented in owner class. Very useful for incremental developing of reports.
:CALL_ALWAYS_PROTOTYPE = 3:  Call methods in prototype class for any action.


Usually the method is called once before calling the run() method. But it is
also possible to alter the call action at any time. 

.. code-block:: php

   // $rep has a reference to phpReport object 
   $rep->setCallAction(Report::CALL_EXISTING);
   $rep->setCallAction(Report::CALL_ALWAYS);
   $rep->setCallAction(Report::CALL_PROTOTYPE);
   $rep->setCallAction(Report::CALL_ALWAYS_PROTOTYPE);

The prototying class is a good example how flexible a report can be. 
   