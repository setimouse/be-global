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
    protected static function getDBProxy($db = null) {
        if (is_null($db)) {
            $db = static::defaultDB();
        }
        DAssert::assert(!is_null($db), 'db should not be null!');

        $dbConfig = Config::runtimeConfigForKeyPath('database.$.'.$db);
        $dbProxy = new DBProxy($dbConfig);
        return $dbProxy;
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

}
