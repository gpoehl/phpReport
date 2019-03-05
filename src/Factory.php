<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace gpoehl\phpReport;

/**
 * Description of Factory
 *
 * @author Günter
 */
class Factory {

    const XS = 5;
    const REGULAR = 10;
    const XL = 15;

    public static function cumulator(MajorProperties $mp, int $maxLevel, int $typ = self::REGULAR) {
        switch ($typ) {
            case self::REGULAR:
                return new Cumulator($mp, $maxLevel);
            case self::XS:
                return new CumulatorXS($mp, $maxLevel);
            case self::XL:
                return new CumulatorXL($mp, $maxLevel);
        }
        throw new \Exception("Invalid typ ($typ) on cumulator request.");
    }

    public static function sheet(MajorProperties $mp, int $maxLevel, int $typ = self::XS, $fromKey = null, $toKey = null) {
        $cumulator = self::cumulator($mp, $maxLevel, $typ);
        if ($fromKey === null) {
            return new sheet($cumulator);
        } 
          return new FixedSheet($cumulator, $fromKey, $toKey);
    }

    public static function collector() {
        return new Collector();
    }
    
    public static function configurator(array $config = null) {
        return new Configurator($config);
    }

    public static function properties() {
        return new MajorProperties();
    }

}
