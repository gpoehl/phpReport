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

This will call the same method in the prototype object.


The other way is setting the runtime option by calling the
setruntimeOption(RuntimeOption $runtimeOption, PrototypeInterace $prototype = null) method by providing one of the RuntimeOption enum values.

:Default:  Call methods in target class only when implemented. 
:Magic:  Call also not existing methods in owner class. Use _magic() in the target class to avoid runtim errors
:Prototype:  Call prototype for methods not implemented in target class.
   Very useful for incremental developing of reports.
   The prototype object might have on option to return any value for beforeGroup and afterGroup actions.
:PrototypeMethods:  Call always prototype even when method exists in owner class.
:PrototypeAll: Call prototype for all actions which are not callables and action is not false.

The second parameter allows setting an prototye object. This can be any class which implemente the PrototypeInterface.
 

Usually the method is called once before calling the run() method. But it is
also possible to alter the call action at any time.

.. code-block:: php

   // $rep has a reference to phpReport object
   $rep->setRuntimeOption(RuntimeOption::Default);
   $rep->setRuntimeOption(RuntimeOption::Magic, new MyOwnPrototype(exectueBeforeAndAfter:false));
   $rep->setRuntimeOption(RuntimeOption::Prototype);
   $rep->setRuntimeOption(RuntimeOption::PrototypeMethods);
   $rep->setRuntimeOption(RuntimeOption::PrototypeAll);


The default prototying class is a good example how flexible a report can be.
