<?php

/**
 * Configuration file to replace default actions.
 * 
 * %s will be replaced by group or dimension name
 * $S will be replaced by ucfirst of group or dimension name
 * $n will be replaced by the numeric ID of the group or dimension
 */
return [
    // Use numberedActions instead of namedActions
    // 'useNumberedActions' => true, 
    
    // Name of grand total group.
    'totalName' => 'total',
    // The name of the detail group (lowest group level)
    'detailName' => 'detail',
    
    // Map action keys to actions using group or dimension names 
    'defaultActions' => [
        'Start' => 'start',
        'Finish' => 'finish',
        'TotalHeader' => 'header%S',
        'TotalFooter' => 'footer%S',
        'NoData' => '<br><strong>No data found</strong><br>', // Dimension = 0
        'DetailHeader' => 'header%S',
        'Detail' => '%s',
        'DetailFooter' => 'footer%S',
        'GroupBefore' => 'before%S',
        'GroupFirst' => 'first%S',
        'GroupHeader' => 'header%S',
        'GroupFooter' => 'footer%S',
        'GroupAfter' => 'after%S',
        'GroupLast' => 'last%S',
        'DimNoData' => 'noData%S',
        'DimDetail' => 'detail%S',
        'DimNoGroupChange' => ["Current row in dimension %s didn't trigger a group change.", \E_USER_ERROR],
    ],
    // Map action keys to actions using numeric group or dimension ID's 
    'numberedActions' => [
        'TotalHeader' => 'header0',
        'TotalFooter' => 'footer0',
        'DetailHeader' => 'header%n',
        'DetailFooter' => 'footer%n',
        'GroupBefore' => 'before%n',
        'GroupFirst' => 'first%n',
        'GroupHeader' => 'header%n',
        'GroupFooter' => 'footer%n',
        'GroupAfter' => 'after%n',
        'GroupLast' => 'last%n',
        'DimNoData' => 'noData%n',
        'DimDetail' => 'detail%n',
        'DimNoGroupChange' => ["Current row in dimension %s (ID = %n) didn't trigger a group change.", \E_USER_ERROR],
    ],
   
        /*  @var Classname for default output handler */
        //'outputHandler' => output\StringOutput::class, 

        /** @var Classname for default prototye class */
        //'prototype' => Prototye::class,
];

