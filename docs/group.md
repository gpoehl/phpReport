# Define group changes
To let **phpReport** handle group changes you need to declare where the values are coming from.
This might be an array element when data row is an array, object property for objects or a closure.

```php
$rep = new report($this);
$rep->group('country', 'country')
$rep->group('year', function($row, $rowKey){return substr ($row->birthdate,0,4);})
```


In the example above we declared two groups. The first got the name country, the second the name year. You can access all group related values by using this names or by their group level.

Note: Group level 0 is always the top level which is called grandTotal.

<h1>Group changes</h1>

The most important task of <strong>phpReport</strong> is to manage group changes.
<br>
Once you have declared what values should be monitored phpReport compares values from a previous row with values from the current row and executes defined actions when the values are not equal.
<br>
Declaring group changes is faily simple by just calling the group method.
<br>
The group method has the following signature:
<div class=code>
group($name, $value, $headerAction = null, $footerAction = null): Report
<\div>

$name: the name of the group. This is used to build method names to be called. All group related values (including cumulated values from sum or sheet functions as well as row and group counters) can be retrieved by this group name or the group level.
$value: The attribute name which holds the value when the row is an object or the key of the array element when the row is an array. You can also pass an closure which has a signature of $row and $rowKey.
$headerAction: When this value is null the default action will be executed. You might name an other method, pass a closure or just an string. When you pass a string this will be appended to $output variable.
$footerAction: Same as $headerAction

As the group method returns the current report object you can chain multiple calls.

Example
<div class=code>
$rep = (new Report ($this))
->group ('region', 'region')
->group ('year' function($row, $rowKey) {return substr($row->saleDate(0,49,})
->group ('month' function($row, $rowKey) {return substr($row->saleDate(6,2);})
->group ('customer, 'customerID')
<\div>

The above example declares four groups to be monitored. All related group values can be accessed by the group names or by the group levels. Please note that level 0 is always the grand total level.

For each declared group a group counter will be instantiated and incremented when a group change occurs.
