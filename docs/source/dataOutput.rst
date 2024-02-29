Data output
===========

The basic idea behind data input is also true for data output. |project_name| doesn't 
create any output itself. 

|project_name| assists you collecting your output by writing returned values
of action methods or defined action strings to the output object.

There is no need to return any output from this methods. You might handle your
output also completely on your own.

|project_name| comes with 2 output classes named StringOutput and BandOutput. Data 
written do the StringOutput object will just be appended at the end of the output.

BandOutput offers much more flexiblity and options. Output will at first grouped by header,
data and footer bands and eventually sorted and returned. Without any specify manipulation
of the output the result is the same as from StringOutput. 

Using external components like mpdf, tcpfd, phpSpredsheet, PHPWord and of course normal CSS
will help to format the output as you like.

.. note:: The value of the output object will be returned from the run() and the end() method.
