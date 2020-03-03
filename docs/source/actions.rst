Actions
=======

Before we start to learn any details about using |project_name| we need to know
something about actions.

Calling the run() method starts the execution and |project_name| takes control over the 
program flow. Whenever an important event occurs an action might be exectued.

Typical actions are: 

* call a method in the target object (The target parameter of the report class)
* append a string to the output
* execute a callable
* call a method in the prototyp class

Within the configuration file you can specify exactly what action will be executed.
During instantiation of the report class you might replace some of the configuration
parameters. 

Events like a group change will trigger an individual groupHeader action for defined 
groups. To be able to execute different action types the group() 
method also allows replacing the default action.
  
The default rules are simple:
1) If action is a string this string will be appended to $output.
2) If action is a method name the method will only be called when the method exist.
The returned value will be appended to $output.
3) If action is a callable the returned value will be appended to $output.

The above rules can be altered by calling the setCallOption(). 

.. include:: actionMethods.rst


     
   
   





