# Collector
Cumulating values is very powerful feature of phpReport.

For each value to be cumulated an cumulator object will be instantiated. These objects will be hold as items in a collector class.

By default phpReport instatiates three collectors.



<ul>
  <li>A row counter collector</li>
  <li>A group counter collector</li>
  <li>A collector for declared values to be cumulated</li>
</ul>


head2: Row counter collector
The row counter collector is named rc. For each data dimension one cumulator object will be instantiated.

head2: Group counter collector
The group counter collector is named gc. For each defined group one cumulator object will be instantiated.

head2: Total colletor
A collector for declared values to be cumulated.
