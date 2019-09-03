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
