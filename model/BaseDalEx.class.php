<?php
/**
 * description
 *
 * Filename: BaseDalEx.class.php
 *
 * deprecated since 2015 7 20
 *
 * @author liyan
 * @since 2014 9 16
 */
abstract class BaseDalEx_Deprecated extends BaseDal {

    protected static function result($ret, $dbProxy) {
        if (false === $ret) {
            throw new DBException($dbProxy->error(), $dbProxy->errno());
        }
        return $ret;
    }

    public static function totalCount() {
        return parent::foundrows();
    }

}
