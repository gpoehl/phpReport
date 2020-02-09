Actions
=======

Before we start to learn any details about using phpReport we need to know
something about actions.

As soon as you call the run method phpReport checks what has be done next. Whenever
an event occurs phpReport executes an action.

Executing an action can result in 

* call a method in the target class (your report class)
* call a method in any class
* append a string to the output
* execute a closure
* call a method in the prototyp class
* do nothing

The kind of actions to be executed as well as the method names to be called will
be declared in the configuration file. This configuration can be altered during
instantiation of phpReport and also when calling data and group methods.

Each action is related to an action key.

Actions will be executed following this rules:
1) Is an action an string the string will be appended to $output.
2) If action is a method name the method will called when the method exist.
3) x
4) Y
 
.. include:: actionMethods.rst


     
   
   





