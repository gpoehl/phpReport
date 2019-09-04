**phpReport** is designed to be very flexible.
Before you begin creating reports have a look at the config.php file and alter default values to meet your requriements and follow your business rules.

All config parameters have default rules. Uncommend only those lines where you want to change the defaults.
While initializing a new **phpReport** you might overwrite the parameters when needed. 

That's the place where you define the type of actions to be performed or the method names.

Actions have a key which will translated in real action names.

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


