<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright © Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

namespace gpoehl\phpReport;

/**
 * Description of Actions
 *
 */
Enum Actionkey  {

    case Start;
    case Finish;
    case TotalHeader;
    case TotalFooter;
    case NoData;
    case DetailHeader;
    case Detail;
    case DetailFooter;
    case GroupBefore;
    case GroupFirst;
    case GroupHeader;
    case GroupFooter;
    case GroupLast;
    case GroupAfter;
    case DimNoData;
    case DimDetail;
    case DimNoGroupChange;

    public function group(): string {
        return match ($this) {
            self::GroupBefore, self::GroupFirst, self::GroupHeader, self::GroupFooter, self::GroupLast, self::GroupAfter => 'group',
            self::DetailHeader, self::Detail, self::DetailFooter => 'detail',
            self::DimDetail, self::DimNoData, self::DimNoGroupChange => 'dim',
            self::TotalHeader, self::TotalFooter => 'total',
            self::Start, self::Finish, self::NoData => 'main'
        };
    }

    public static function fromName(string $name) {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class);
    }

    public static function tryFromName(string $name): self|null {
        try {
            return self::fromName($name);
        } catch (\ValueError $error) {
            return null;
        }
    }

    public static function getKeysByGroup($groupName) {
        return match ($groupName) {
            'group' => [self::GroupBefore, self::GroupFirst, self::GroupHeader, self::GroupFooter, self::GroupLast, self::GroupAfter],
            'detail' => [self::DetailHeader, self::DimDetail, self::DetailFooter],
            'dim' => [self::DimDetail, self::DimNoData, self::DimNoGroupChange],
            'total' => [self::TotalHeader, self::TotalFooter],
            'main' => [self::Start, self::Finish, self::NoData],
        };
    }
}
