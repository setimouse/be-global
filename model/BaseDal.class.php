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

        $key = crc32(serialize($dbConfig));
        if (!array_key_exists($key, self::$dbpool)) {
            self::$dbpool[$key] = new DBProxy($dbConfig);
        }
        $dbProxy = self::$dbpool[$key];

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

    protected static function doDelete($sql, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $ret = $dbProxy->doDelete($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function doUpdate($table, $updates, $where, $limit = 0x7fffffff, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $sql = $dbProxy->updateStatement($table, $updates, $where, $limit);
        $ret = $dbProxy->doUpdate($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function doInsertUpdate($table, $arrIns, $arrUpd, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $sql = $dbProxy->insertOrUpdateStatement($table, $arrIns, $arrUpd);
        Trace::debug($sql);
        $ret = $dbProxy->doInsert($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function doInsert($table, $fields_values, $db = null) {
        $dbProxy = static::getDBProxy($db);
        $sql = $dbProxy->insertStatement($table, $fields_values);
        $ret = $dbProxy->doInsert($sql);
        return self::result($ret, $dbProxy);
    }

    protected static function insertID($db = null) {
        $dbProxy = static::getDBProxy($db);
        $ret = $dbProxy->insertID();
        return self::result($ret, $dbProxy);
    }

    public static function beginTransaction($db = null) {
        $dbProxy = static::getDBProxy($db);
        return $dbProxy->beginTransaction();
    }

    public static function commit($db = null) {
        $dbProxy = static::getDBProxy($db);
        return $dbProxy->commit();
    }

    public static function rollback($db = null) {
        $dbProxy = static::getDBProxy($db);
        return $dbProxy->rollback();
    }

}
