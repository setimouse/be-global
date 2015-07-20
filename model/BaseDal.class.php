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

    protected static $dbpool = array();

    protected static function defaultDB() {
        return null;
    }

    public static function getDBQuery($db = null, $rw = 'w') {
        if (is_null($db)) {
            $db = static::defaultDB();
        }
        DAssert::assert(!is_null($db), 'db should not be null!');
        DAssert::assert(in_array($rw, array('r', 'w')), 'illegal rw');

        $dbConfig = Config::runtimeConfigForKeyPath('database.$.'.$db);
        $dbReadWrite = new DBReadWrite($dbConfig);
        $dbConnection = $dbReadWrite->getConnection('DBMysqli', $rw);
        $dbQuery = new DBQuery($dbConnection);

        return $dbQuery;
    }

    protected static function result($ret, DBQuery $dbQuery) {
        if (false === $ret) {
            throw new DBException($dbQuery->error(), $dbQuery->errno());
        }
        return $ret;
    }

    protected static function rs2array($sql, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $ret = $dbQuery->rs2array($sql);
        return static::result($ret, $dbQuery);
    }

    protected static function rs2keyarray($sql, $key, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $ret = $dbQuery->rs2keyarray($sql, $key);
        return static::result($ret, $dbQuery);
    }

    protected static function rs2grouparray($sql, $groupkey, $rowkey = null, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $ret = $dbQuery->rs2grouparray($sql, $groupkey, $rowkey);
        return static::result($ret, $dbQuery);
    }

    protected static function rs2rowline($sql, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $ret = $dbQuery->rs2rowline($sql);
        return static::result($ret, $dbQuery);
    }

    protected static function rs2rowcount($sql, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $ret = $dbQuery->rs2rowcount($sql);
        return static::result($ret, $dbQuery);
    }

    protected static function realEscapeString(&$str, $db = null, $rw = 'r') {
        $dbQuery = static::getDBQuery($db, $rw);
        $str = $dbQuery->rs2keyarray($str);
        return $str;
    }

}
