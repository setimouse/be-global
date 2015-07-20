<?php
/**
 * DBHelper
 *
 * @author liyan
 * @since 2015 7 16
 */
class DBConnection {

    protected $dbAdapter;

    function __construct(DBAdapter $dbAdapter) {
        $this->dbAdapter = $dbAdapter;
    }

    public function connect($config) {
        $hostport = $config['hosts'][array_rand($config['hosts'])];

        $dbAdapter = $this->dbAdapter;
        try {
            $dbAdapter->host = $hostport['h'];
            $dbAdapter->port = $hostport['p'];
            $dbAdapter->username = $config['username'];
            $dbAdapter->password = $config['password'];
            $dbAdapter->dbname = $config['dbname'];
            $dbAdapter->connect();

            $dbAdapter->query('SET NAMES "UTF8"');
        } catch (Exception $e) {
            throw new Exception("connect db failed", 1);
        }

        return $this;
    }

    public function close() {
        $this->dbAdapter->close();
    }

    public function query($sql) {
        try {
            return $this->dbAdapter->query($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function error() {
        return $this->dbAdapter->error();
    }

    public function errno() {
        return $this->dbAdapter->errno();
    }

}
