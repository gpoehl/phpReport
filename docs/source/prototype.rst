.. _prototype-label:

The normal process flow can be altered by setting the runTimeOption. Main reason
is to use prototyping or to allow working with __magic() methods.

Prototyping
===========

Prototyping helps in various ways to get information about the current status of the processed data.

First one is by calling the prototype method.

.. code-block:: php

   $rep->prototype();

This call will be passed to a method in the prototype object which in turn delivers
some detailed data of the current status.  

Using the second scenario actions might be executed in the prototype class instead
or in addition to the application target object. The default prototyp class provides
the currently processed row, names of methods which whould habe been called in normal
mode, the value of group fields and some values out of the aggregated fields.

Without writing any line of code you'll get a basic idea of the contents of your report.

.. code-block:: php

   $rep->setRuntimeOption($option, PrototypeInterface $prototype = null);

When no $prototype is given the default prototype object will be used.


.. tip:: Protyping for unit tests.
    Creating a specific prototype class might help to create ouptut to be tested
    within unit tests.


The other way is setting the runtime option by calling the
setruntimeOption(RuntimeOption $runtimeOption, PrototypeInterface $prototype = null) method by providing one of the RuntimeOption enum values.

.. php:method:: setRuntimeOption($option, PrototypeInterface $prototype = null):report

 :param Runtimeoption $option: The option to be selected.

 :param PrototypeInterface $prototype: Prototyp class to be used instead of the default one.
 
 :returns: $this which allows method call chaining.


.. list-table:: Runtime options
        :widths: auto
        :header-rows: 1

        * - Option
          - Description
        * - Default
          - Call methods in target class only when implemented.
        * - Magic
          - Call also not existing methods in owner class. Use _magic() in the target class to avoid runtime errors.
        * - Prototype
          - Call prototype for methods not implemented in target class.
            Very useful for incremental developing of reports.
            The prototype object might have on option to return any value for beforeGroup and afterGroup actions.
        * - PrototypeMethods
          - Call always prototype even when method exists in owner class.
        * - PrototypeAll
          - Call prototype for all actions which are not callables and action is not false.
      

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
