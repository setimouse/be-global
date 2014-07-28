<?php
/**
 * dal base
 *
 * Filename: BaseDal.class.php
 *
 * @author liyan
 * @since 2014 4 24
 */
abstract class BaseDal {

    protected static function defaultDB() {
        return null;
    }

    /**
     *
     *
     * @param DBProxy $db
     */
    public static function getDBProxy($db = null) {
        if (is_null($db)) {
            $db = static::defaultDB();
        }
        DAssert::assert(!is_null($db), 'db should not be null!');

        $dbConfig = Config::runtimeConfigForKeyPath('database.$.'.$db);
        $dbProxy = new DBProxy($dbConfig);
        return $dbProxy;
    }

    public static function getInsertId($db = null) {
        $dbProxy = static::getDBProxy($db);
        return $dbProxy->insertID();
    }

    protected static function result($ret, $dbProxy) {
        if (false === $ret) {
            return MResult::result(MResult::FAIL, array('errno' => $dbProxy->errno(), 'error' => $dbProxy->error()));
        }
        return MResult::result(MResult::SUCCESS, $ret);
    }

    protected static function rs2array($sql, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $ret = $dbProxy->rs2array($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function rs2rowline($sql, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $ret = $dbProxy->rs2rowline($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function rs2firstvalue($sql, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $ret = $dbProxy->rs2firstvalue($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function doInsert($table, $fields_values, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $sql = $dbProxy->insertStatement($table, $fields_values);
        $ret = $dbProxy->doInsert($sql);
        return self::result($ret, $dbProxy);
    }

}
