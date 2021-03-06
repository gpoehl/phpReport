Configuration
=============

|project_name| lets you configure the real action to be performed for events 
which triggers one or more actions.

Whenever an event occurs the action defined to an action key will be performed.

For each possible action to be executed you can declare what should be done.
Each action key has a default action assigned. Within the **config.php** file the
default actions might be adjusted to meet your personal favorites or to follow 
business rules of your orgisation.


.. csv-table:: Action types
   :header: "Action typ", "Action defined by"
   :widths: 50, 100

   "Call a method in target class", "Action is a legal method name"
   "Call a method in any class", "Action is an array ([class, method])"
   "Ouput a string", "Action is not a legal method name or begins with an :"
   "Throw an error", "Action begins with error:"
   "Raise a warning", "Action begins with warning:"
   "Do nothing", "Action equals false"  

The following table lists all possible actions, assigned defaults and notices
how the % sign the will be replaced during run time.

    .. list-table:: Action mapping
        :widths: auto
        :header-rows: 1

        * - action key
          - default value
          - % replaced by
        * - init
          - init
          - 
        * - close
          - close
          - 
        * - totalHeader    
          - %Header
          - $grandTotalName
        * - totalFooter    
          - %Footer
          - $grandTotalName
        * - groupHeader    
          - %Header
          - | group name or
            | group level
        * - groupFooter    
          - %Footer
          - | group name or
            | group level
        * - detail    
          - detail
          - 
        * - noData    
          - <br><strong>No data found </strong><br>
          - 
        * - noData_n    
          - noDataDim%
          - dimension id
        * - noGroupChange    
          - | error:Current row in dimension % didn't 
            | trigger a group change.
          - dimension id


Next to the actions parameter you can also declare the following parameters:

.. php:attr:: grandTotalName

    The name for the grand total group. Will also be used to 
    build actions for action keys totalHeader and totalFooter.

    .. note:: You can always access grand totals by group level of 0.

.. php:attr:: buildMethodsByGroupName 

    When true the % sign actions related to groupHeader and 
    groupFooter will be replaced by the group names. When false by the numeric group
    level.

That's the place where you define the type of actions to be performed or the method names.

Actions have a key which will translated to real action names.

Per default the action 'init' will call a method called 'init'. That's fairly simple.
The same is true alse for the 'detail' and 'close' actions.

The deaufault action 'totalHeader' is '%Header'. The % sign will be replaced by the value of the 'grandTotalName'.
Assuming 'grandTotalName' is unchanged and has the value of 'total' the action to be performed is the 'totalHeader' method.
The same rule applies to the 'totalFooter' action.

Similar rules are true for the 'groupHeader' and 'groupFooter' actions. But as we need an action for each defined group the % sign will be replaced by the group name.
Example: 
You defined group changes for 'region'. 'customer' and 'invoice'. 

region regionHeader regionFooter
customer customerHeader customerFooter
invoi invoiceHeader and invoiceFooter

You like it the other way. Then name the groupHeader action 'head_%' and footerAction 'foot_%'.

region head_region foot_region
customer head_customer foot_customer
invoice head_invoice foot_invoice

The same rule with 'buildMethodsByGroupName' set to 'ucfirst' will result in these actions:
region head_Region foot_Region
customer head_Customer foot_Customer
invoice head_Invoice foot_Invoice

The rules above are very simple to follow so that's nothing really sopisticated.

Setting the 'buildMethodsByGroupName' set to 'false' will replace the % sign by the group level.
The last example would call those methods

region head_1 foot_1
customer head_2 foot_2
invoice head_3 foot_3

What might look very strange at the first sigth is very powerful when you want to write very flexible apps. Changing the second group from 'customer' to 'branch' doesn't change the action group names. While knowing that the group value is passed to the method as a parmeter and that you have easy access to the group names gives you nearly unlimited choices.

Your are not limited to declare how method names are build.

For some actions it is suitable to define a string. Then the string will be appende to the $output variable.
A good example is the 'noData' action.
In many cases you won't instantiate **phpReport** when your data query doen't return any data. But you might also in this cases a report with nice header and footer and in between just printint a message like "Sorry, we couldn't find any data".
So it's not worth to create a method in each report which returns such a string.
The solution is to declare this string as a default. A : sign at the beginning makes sure that this string is always treated as a string and can not be mixed up with a method name.

The % sign in 'noData_n' actions will be replaced by the number of current dimension when the current dimension is greater than 0. See multi dimensional data for more details. 

The very last parameter is called 'userConfig'. This is an optional way how you can pass data around. **phpReport** isself does't use this values.


.. note:: 
    All directives can be altered when initializing a new |project_name|. Some
    even when calling a method. 
