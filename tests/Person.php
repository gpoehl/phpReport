<?php

declare(strict_types=1);


/**
 * Test class to represent a data row.
 */
class Person
{

    public const MALE = 1;
    public const FEMALE = 2;
    public static $statPers = 'static person';
       
    public function __construct(public int $id,
            public string $firstName,
            public string $sureName,
            public int $age,
            public int $gender = self::MALE,
    ) {
        
    }

    public function getName() {
        return $this->firstName . ' ' . $this->sureName;
    }

    public function getYearDiff(int $year) {
        return $year - $this->age;
    }
    
    public function say(... $values) {
        return implode (' ', $values) . ' ' . $this->sureName;
    }
    
     public static function staticAdd(... $values) {
        return array_sum($values);
    }

}