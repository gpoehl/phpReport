Datainput

**phpRepot** doesn't read data itself.
You should use your own (and well known) mehtods for data retrieval as before.
This is very useful when you're using a php framework which creates objects from database rows.

**phpReport** directly works with this objects which also means that you have full access to your business logic implemented in your models.

While reading data from your database you often need to yoin data from several tables or views. You can do that with ++phpReport** as well. But you can also just read data from the first table and read data from the next tables via the data () method. 

This allows reading data from any database, or any file types like csc, xml, json, excel sheets and also just passing any array as data input.

A single data row can be an object or an array. You can pass your data row by row, by one or more chuncks or all at once to phpReport.

Passing data one by one eliminates the need to build an array (or iterable). Just call the next method for each row.

To pass chunks or the whole dataset to phpReport call the run mehod. For chunks you must set the second paramet (finalize) to false.
Call run for the last chunck with finalize = true or call the end mehthod afterwords.

