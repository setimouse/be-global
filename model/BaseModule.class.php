<?php
/**
 * description
 *
 * Filename: BaseModule.class.php
 *
 * @author liyan
 * @since 2015 7 16
 */
abstract class BaseModule {

    protected $database;

    protected static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function database() {
        return $this->database;
    }

    public function setDatabase($database) {
        $this->database = $database;
    }

    abstract public function createTableSQL();

}
