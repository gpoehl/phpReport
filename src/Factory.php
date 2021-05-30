<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */
declare(strict_types=1);

namespace gpoehl\phpReport;

use const Report;
use InvalidArgumentException;

/**
 * Instantiate objects for phpReport
 */
class Factory {

    /**
     * Instantiate a calculator object based on $typ parameter
     * @param MajorProperties $mp The majorProperies object
     * @param int $maxLevel The maximum group level for the cumulator
     * @param int $typ Depending on $typ one the cumulator classes will be selected.
     * Report::XS for the CalculatorXS class 
     * Report::REGULAR for the Calculator class 
     * Report::XL for th CalculatorXL class 
     * @return Calculator|CalculatorXS|CalculatorXL
     * @throws InvalidArgumentException
     */
    public static function calculator(MajorProperties $mp, int $maxLevel, int $typ = Report::REGULAR): AbstractCalculator {
          return match ($typ) {
            Report::REGULAR => new Calculator($mp, $maxLevel),
            Report::XS => new CalculatorXS($mp, $maxLevel),
            Report::XL => new CalculatorXL($mp, $maxLevel),
            default => throw new InvalidArgumentException("Invalid typ ($typ) on cumulator request."),
        };
    }

    /**
     * Instantiate a sheet object
     * Sheet object might be normal or with fixed number of items (columns).
     * Each sheet element is represented by an calculator object of the same typ.
     * @param MajorProperties $mp The majorProperies object
     * @param int $maxLevel The maximum group level for all cumulators within the sheet
     * @param int $typ The calculator object typ. @see $this->calculator()
     * @param mixed $fromKey When $fromKey is null a normal sheet will be instantiatd.
     * When a value is given this is the first item name within a fixed sheet.
     * When $fromKey is an array then all values are used as sheet items.
     * @param mixed $toKey The last sheet item. $fromKey will be incremented until
     * $toKey is reached.
     * @return Sheet|FixedSheet
     */
    public static function sheet(
            MajorProperties $mp,
            int $maxLevel,
            int $typ = Report::XS,
            $fromKey = null,
            $toKey = null
    ): AbstractCollector {
        $calculator = self::calculator($mp, $maxLevel, $typ);
        if ($fromKey === null) {
            return new Sheet($calculator);
        }
        return new FixedSheet($calculator, $fromKey, $toKey);
    }

    /**
     * Instantiate a new collector object
     * allow string access on numeric item keys
     * @return Collector
     */
    public static function collector() {
        return new Collector();
    }

    /**
     * Instantiate a new major properties object
     * @return MajorProperties
     */
    public static function properties() {
        return new MajorProperties();
    }

}
