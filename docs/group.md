# Define group changes
To let **phpReport** handle group changes you need to declare where the values are coming from.
This might be an array element when data row is an array, object property for objects or a closure.

```php
$rep = new report($this);
$rep->group('country', 'country')
$rep->group('year', function($row, $rowKey){return substr ($row->birthdate,0,4);})

´´´
In the example above ...
