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
     * Instantiate a cumulator object based on $typ parameter
     * @param MajorProperties $mp The majorProperies object
     * @param int $maxLevel The maximum group level for the cumulator
     * @param int $typ Depending on $typ one the cumulator classes will be selected.
     * Report::XS for the CumulatorXs class 
     * Report::REGULAR for the Cumulator class 
     * Report::XL for th CumulatorXL class 
     * @return Cumulator|CumulatorXS|CumulatorXL
     * @throws InvalidArgumentException
     */
    public static function cumulator(MajorProperties $mp, int $maxLevel, int $typ = self::REGULAR): AbstractCumulator {
        switch ($typ) {
            case Report::REGULAR:
                return new Cumulator($mp, $maxLevel);
            case Report::XS:
                return new CumulatorXS($mp, $maxLevel);
            case Report::XL:
                return new CumulatorXL($mp, $maxLevel);
        }
        throw new InvalidArgumentException("Invalid typ ($typ) on cumulator request.");
    }

    /**
     * Instantiate a sheet object
     * Sheet object might be normal or with fixed number of items (columns).
     * Each sheet element will be an cumulator object of the same typ.
     * @param MajorProperties $mp The majorProperies object
     * @param int $maxLevel The maximum group level for all cumulators within the sheet
     * @param int $typ The cumulator object typ. @see $this->cumulator()
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
        $cumulator = self::cumulator($mp, $maxLevel, $typ);
        if ($fromKey === null) {
            return new Sheet($cumulator);
        }
        return new FixedSheet($cumulator, $fromKey, $toKey);
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
     * Instantiate a new configurator object
     * @param array $config The configuration parameter
     * @return Configurator
     */
    public static function configurator(array $config = null) {
        return new Configurator($config);
    }

    /**
     * Instantiate a new major properties object
     * @return MajorProperties
     */
    public static function properties() {
        return new MajorProperties();
    }

}
