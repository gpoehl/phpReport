Getting values
==============

All initialisation methods have parameters called source and params to declare
from where and how subsequent actions will get the requried data (e.g. The value
for group changes).

The parameter $params gets optional variadic arguments which will be passed unpacked
as the last parameters to callables specified in $source. 

Specialized getter classes are available to get the requested values. You may pass
a getter object of your choice to the source parameter or let |project_name| choose
the right one.

The latter option is usually the easiest one.

Source might get a scalar value or an array with up to three parameters. 

Scalar values might be:
a) The array key or property name depending of the type of data data row.
b) A closure which expects $row and $rowKey as the first two parameters.
c) A getter object extending BaseGetter.
d) An non associative array or associated array having name, target and selector keys for all 
other sources PHP has to offer.

0 - name: The object member name or a closure.
1 - target: Object or class name where the object member name relates to.
        Null - The current row object. 
        True - The default target
        When static properties or constants are requested and a object is
        given the fully qualified class name will derived from the object.
1 - selector: Null - When target is not Null name is a property of the given object.
                 When target is Null (the current row object) and name is 
                 a closure it will - in contrast to the closure as a scalar source - 
                 called without $row and $rowKey. 
                 When name is not a closure name represents a method or static method. 
                 Methods will be invoked without $row and $rowKey parameters. 
          Bool - Name is a callable (method, static method or closure). True will pass $row
                 and $rowKey to this callable as the first two parameters while 
                 False will not pass them. 
          stat - String to indicate that name is a static class property.
          const - String to indicate that name is a class constant. 
                  

For method chaining use closures, method calls or dedicated getter objects.


The following examples shows all combinations:

'abc'                            Array key or row class property name
2                                Array key
fn($row, $rowKey) => (...)       Closure with gets $row and $rowKey
new(xxxGetter(..))               Dedicated getter object

['myMethod']                    Method of current row object.
['myMethod', null]              Same as before.
['myMethod', null, null]        Same as before.
['myMethod', null, false]       Same as before.
['myMethod', null, true]        Same as before but $row and $rowKey will be passed.


['abc', true]                    Target object property name.
['abc', $object]                 Any object property name.


['abc', true, false]             Target class or object method.
['abc', true, true]              Same as before but expecting $row and $rowKey as parameters.

['abc', 'xyz', false]            Method in xyz class or object.
['abc', 'xyz', true]             Same as before but expecting $row and $rowKey as parameters.

['abc', null, 'const']           Constant from row class.
['abc', true, 'const']           Constant from target class.
['abc', 'xyz', 'const']          Constant from xyz class.

['abc', null, 'stat']            Static property from row class.
['abc', true, 'stat']            Static property from target class.
['abc', 'xyz', 'stat']           Static property from xyz class.

[fn(...) => (...)]               Closure not getting $row and $rowKey.
[fn(...) => (...), null, false]  Same as before.
[fn($, $k) => (...), null, true] Closure getting $row and $rowKey as parameters. 
fn($r, $k) => (...)              Same as before. 
