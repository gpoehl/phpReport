Getting values
==============

All initialisation methods have parameters called **$source** and **$params** to declare
how related actions like evaluating a group change will get the required data.

The parameter `$params` may hold variadic arguments which will be passed unpacked
(after $row and $rowKey) to callables specified in `$source`. 

Specialized getter classes are available to get the requested values. You may pass
a getter object of your choice to the source parameter or let |project_name| choose
the right one. The latter option is usually the easiest one.

Source might get a scalar value or an array with up to three parameters where
scalar values might be:

- An array key or property name depending of the type of data data row.
- A closure which expects $row and $rowKey as the first two parameters.
- A getter object which implements the GetValueInterface.

For all other sources PHP has to offer `$source` must be an array with up to 3 elements.

 .. csv-table:: Source array parameters
   :header: "Key", "Content"
   :widths: 15, 170

   "0", "The object member name or a closure."
   "1", "The class or object where the object member belongs to.
    * Null - The current row object (default)  
    * True - The default target (Value of the target parameter of report class).
    * Object or class name. Object might also be used to access a constant.

    " 
    "2", "Kind of object member
    * | **Null** - Sets the most logical defaults.
      |      For closures same as `False` (in contrast to the closure as a scalar source).
      |      Object property when key[1] is not null.
      |      Method of the current row object.   
    * | Bool - Name is a callable (method, static method or closure). 
      |        `True` will pass $row and $rowKey as the first parameters to this callable.
      |        `False` will not pass $row and $rowKey to this callable.
    * **stat** - Name is a static class property.
    * **const** - Name is a class constant.     

    "

                  
For method chaining use closures, method calls or dedicated getter objects.


The following examples shows all combinations:

 .. csv-table:: Source parameter examples
   :header: "Source", "Description"
   :widths: 30, 120

   "| \'abc\'
   | \'a b c'\
   | 2", "Property of row object or array item."
   "[\'abc\', true]", "Property in default target."
   "[\'abc\', $object]", "Property in $object."
   "| fn($r, $k) => (...)
   | fn($r, $k, ...$params) => (...)
   | [fn($r, $k) => (...), null, true]
   | [fn($r, $k, ...$p) => (...), null, true]", "Closure getting $row and $rowKey."
   "| [fn(... $params) => (...)]
   | [fn(... $params) => (...), null, false] ", "Closure **not** getting $row and $rowKey"
   "| [\'abc\']
   | [\'abc\', null]
   | [\'abc\', null, null]
   | [\'abc\', null, false]", "Method of current row object."
   "[\'abc\', null, true]", "Static method in current row class getting $row and $rowKey."
   "| [\'abc\', true, false]
   | [\'abc', true, true]", "| Method or static method in default target.
   | Same as before but getting $row and $rowKey."
   "| [\'abc\', $obj, false]
   | [\'abc', \'xyzClass\', false]
   | [\'abc', $obj, true]
   | [\'abc', \'xyzClass\', true]", "| Method or static method in $obj object.
   | Static method in \'xyzClass\ class.
   | (Static) method in $obj getting $row and $rowKey.
   | Static method in \'xyzClass\' class getting $row and $rowKey."
   "| [\'abc\', null, \'stat'\]
   | [\'abc\', true, \'stat\']
   | [\'abc\', $obj, \'stat\'] 
   | [\'abc\', 'xyz', \'stat\'] 
   ", "| Static property from row class,
   | default target,
   | $obj object,
   | \'xxz\ class."
   "| [\'ABC\', null, \'const'\]
   | [\'ABC\', true, \'const\']
   | [\'ABC\', $obj, \'const\'] 
   | [\'ABC\', 'xyz', \'const\'] 
   ", "| Constant from row class,
   | default target,
   | $obj object,
   | \'xxz\ class."
   "new xxxGetter(...)", "Dedicated getter object"
