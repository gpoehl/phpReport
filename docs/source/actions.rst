Actions
=======

Calling the run() method starts the execution and |project_name| takes control over the 
program flow. Whenever defined events are detected related action will be invoked.

Typical actions are: 

* call a method in the target object (The target parameter of the report class)
* append a string to the output
* execute a callable
* call a method in the prototyp class

One can specify exactly what action will be executed by configuration.

The default rules are simple:
1) If action is a string this string will be appended to the output object.
2) If action is a method name  a callablthe method will only be called when the method exist.
The returned value will be appended to the output object.
3) If action is a callable the returned value will alseo appended to the output object.


.. include:: actionMethods.rst