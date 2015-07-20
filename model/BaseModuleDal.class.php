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

    protected static $db;

    protected static function defaultDB() {
        $module = static::module();
        return $module->database();
    }

    abstract protected static function module();

}
