<?php

/**
 * Configuration file to replace default actions.
 */
return [
    
    // Name of grand total group.
    'totalName' => 'total',
 
    // Map action keys to actions using group or dimension names 
    'defaultActions' => [
       'GroupHeader' => '%sFromFile',
     ],
 
     // Map action keys to actions using numeric group or dimension ID's 
    'numberedActions' => [
        'GroupHeader' => 'FromFile%n',
    ],
   
];

