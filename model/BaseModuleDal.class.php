<?php
/**
 * description
 *
 * Filename: BaseModuleDal.class.php
 *
 * @author liyan
 * @since 2015 7 16
 */
abstract class BaseModuleDal extends BaseDal {

    private static $lastQueryMethod;
    private static $lastArgs;

    private static $tryCreateTable = false;

    protected static function defaultDB() {
        $module = static::module();
        return $module->database();
    }

    abstract protected static function module();

    protected static function result($ret, DBQuery $dbQuery) {
        try {
            return parent::result($ret, $dbQuery);
        } catch (DBException $e) {
            if (self::$tryCreateTable) {
                self::$tryCreateTable = false;
                throw $e;
            }

            if (1146 == $e->getCode()) {   # Table doesn't exist, errno: 1146
                self::$tryCreateTable = true;
                $module = static::module();
                $arrSql = $module::createTablesSQL();
                foreach ($arrSql as $sql) {
                    static::doCreateTables($sql);
                }
                return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
            }
            throw $e;
        }
    }

    protected static function doCreateTables($sql, $db = null) {
        $dbQuery = static::getDBQuery($db, 'w');
        $ret = $dbQuery->doCreateTable($sql);
        return static::result($ret, $dbQuery);
    }

    protected static function rs2array($sql, $db = null, $rw = 'r') {
        self::$lastQueryMethod = __FUNCTION__;
        self::$lastArgs = func_get_args();
        return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
    }

    protected static function rs2keyarray($sql, $key, $db = null, $rw = 'r') {
        self::$lastQueryMethod = __FUNCTION__;
        self::$lastArgs = func_get_args();
        return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
    }

    protected static function rs2grouparray($sql, $groupkey, $rowkey = null, $db = null, $rw = 'r') {
        self::$lastQueryMethod = __FUNCTION__;
        self::$lastArgs = func_get_args();
        return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
    }

    protected static function rs2rowline($sql, $db = null, $rw = 'r') {
        self::$lastQueryMethod = __FUNCTION__;
        self::$lastArgs = func_get_args();
        return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
    }

    protected static function rs2rowcount($sql, $db = null, $rw = 'r') {
        self::$lastQueryMethod = __FUNCTION__;
        self::$lastArgs = func_get_args();
        return call_user_func_array(array('parent', self::$lastQueryMethod), self::$lastArgs);
    }

}
