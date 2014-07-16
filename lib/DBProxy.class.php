<?php
/**
 * DBProxy
 *
 * @author liyan
 * @since 2014 4 24
 */
class DBProxy {

    protected static $stat = array('select' => 0, 'insert' => 0, 'update' => 0, 'delete' => 0, 'read' => 0, 'write' => 0);

    protected $dbConfig;

    private $conn_r;
    private $conn_w;

    private $lastUsedConn;

    function __construct($dbConfig) {
        $this->dbConfig = $dbConfig;
        $this->conn_r = $this->conn_w = null;
    }

    protected function getConnection($rw = 'w') {
        DAssert::assert(in_array($rw, array('r', 'w')), 'illegal rw');

        $var_conn = 'conn_'.$rw;
        $conn = &$this->$var_conn;

        if (!is_resource($conn)) {
            $config = Config::configForKeyPath($rw, $this->dbConfig);
            $conn = $this->connect($config);
        }

        $this->lastUsedConn = $conn;

        return $conn;
    }

    private function connect($config) {
        srand(time());
        $dbIndex = rand(0, count($config['hosts']) - 1);
        $host = $config['hosts'][$dbIndex]['h'];
        $port = $config['hosts'][$dbIndex]['p'];
        $username = $config['username'];
        $password = $config['password'];
        $dbname = $config['dbname'];

        $connection = new DBMysqli($host, $username, $password, $dbname, $port);

        $connection->query('SET NAMES "UTF8"');

        return $connection;
    }

    public function beginTransaction() {
        $this->getConnection('w')->query('BEGIN');
    }

    public function endTransaction() {
        $this->getConnection('w')->query('END');
    }

    public function commit() {
        $this->getConnection('w')->query('COMMIT');
    }

    public function rollback() {
        $this->getConnection('w')->query('ROLLBACK');
    }

    public function doInsert($sql)
    {
        self::$stat['insert']++;
        return $this->doWrite($sql);
    }

    public function doUpdate($sql)
    {
        self::$stat['update']++;
        return $this->doWrite($sql);
    }

    public function doDelete($sql)
    {
        self::$stat['delete']++;
        return $this->doWrite($sql);
    }

    public function doSelect($sql)
    {
        self::$stat['select']++;
        return $this->doRead($sql);
    }

    public function doRead($sql) {
        self::$stat['read']++;
        return $this->getConnection('r')->query($sql);
    }

    public function doWrite($sql) {
        self::$stat['write']++;
        return $this->getConnection('w')->query($sql);
    }

    public function affectedRows() {
        return $this->lastUsedConn->affectedRows();
    }

    public function insertID() {
        return $this->lastUsedConn->insert_id();
    }

    public function error() {
        return $this->lastUsedConn->error();
    }

    public function errno() {
        return $this->lastUsedConn->errno();
    }

    public function rs2array($sql)
    {
        $rs = $this->doSelect($sql);
        if (false === $rs) {
            return false;
        }
        $ret = array();
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $ret[] = $row;
            }
        }
        return  $ret;
    }

    public function rs2oneColumnArray($sql) {
        $rs = $this->doSelect($sql);
        if (false === $rs) {
            return false;
        }
        $ret = array();
        if ($rs) {
            while ($row = $rs->fetch_row()) {
                $ret[] = $row[0];
            }
        }
        return  $ret;
    }

    public function rs2keyarray($sql, $key) {
        $rs = $this->doSelect($sql);
        if (false === $rs) {
            return false;
        }
        $ret = array();
        if ($rs) {
            while ($row = $rs->fetch_assoc($rs)) {
                $resultKey = $row[$key];
                $ret[$resultKey] = $row;
            }
        }
        return  $ret;
    }

    public function rs2rowline($sql)
    {
        $rs = $this->doSelect($sql);
        if (false === $rs) {
            return false;
        }
        $ret = $rs->fetch_assoc($rs);
        if (false === $ret) {
            return null;
        }
        return  $ret;
    }

    public function rs2rowcount($sql)
    {
        $ret = $this->rs2firstvalue($sql);
        return $ret;
    }

    public function rs2firstvalue($sql)
    {
        $row = $this->rs2rowline($sql);
        if (false === $row) {
            return false;
        } elseif (null === $row) {
            return null;
        }

        if (!is_array($row)) {
            return false;
        }

        $ret = array_values($row);
        return $ret[0];
    }

    public function rs2foundrows() {
        return $this->rs2firstvalue("SELECT FOUND_ROWS()");
    }

    public function realEscapeString($string) {
        $conn = defaultNullValue($this->lastUsedConn, $this->getConnection('w'));
        return $conn->realEscapeString($string);
    }

    public static function getInsertNum()
    {
        return self::$stat['insert'];
    }

    public static function getDeleteNum()
    {
        return self::$stat['delete'];
    }

    public static function getUpdateNum()
    {
        return self::$stat['update'];
    }

    public static function getSelectNum()
    {
        return self::$stat['select'];
    }

    /**
     * make up insert statement
     *
     * @param string $table
     * @param string $fields_values
     * @param DBProxy $dbProxy
     */
    public function insertStatement($table, $fields_values) {
        $arrayFields = array();
        $arrayValues = array();
        foreach ($fields_values as $field => $value) {
            $arrayFields[] = '`'.$field.'`';
            $arrayValues[] = "'".$this->realEscapeString($value)."'";
        }
        $strFields = join(', ', $arrayFields);
        $strValues = join(', ', $arrayValues);
        $sql = "INSERT INTO $table($strFields) VALUES($strValues)";
        return $sql;
    }

    /**
     * make up insert values statement
     *
     * @param string $table
     * @param array $fields
     * @param array $valueSet
     */
    public function insertMultiStatement($table, $fields, $valueSet) {
        $arrayFields = array();
        $arrayValues = array();
        foreach ($fields as $field) {
            $arrayFields[] = '`'.$field.'`';
        }

        $arrValueSet = array();
        foreach ($valueSet as $values) {
            $arrayValues = array();
            foreach ($values as $val) {
                $arrayValues[] = "'".$val."'";
                // $arrayValues[] = "'".$this->realEscapeString($val)."'";
            }
            $arrValueSet[] = '('.join(',',$arrayValues).')';
        }
        $strFields = join(',', $arrayFields);
        $strValues = join(',', $arrValueSet);
        $sql = "INSERT INTO $table($strFields) VALUES $strValues";
        return $sql;
    }

    /**
     * make up insert or update statement
     *
     * @param string $table
     * @param string $fields_values
     * @param string $updates
     * @param DBProxy $dbProxy
     */
    public function insertOrUpdateStatement($table, $fields_values, $updates) {
        $arrayFields = array();
        $arrayValues = array();
        foreach ($fields_values as $field => $value) {
            $arrayFields[] = '`'.$field.'`';
            $arrayValues[] = "'".$this->realEscapeString($value)."'";
        }
        $strFields = join(', ', $arrayFields);
        $strValues = join(', ', $arrayValues);

        $arrayUpdates = array();
        foreach ($updates as $upKey => $upValue) {
            $arrayUpdates[] = "`".$this->realEscapeString($upKey)."`='".DBProxy::realEscapeString($upValue)."'";
        }
        $strUpdates = join(', ', $arrayUpdates);

        $sql = "INSERT INTO $table($strFields)
                VALUES($strValues)
                ON DUPLICATE KEY UPDATE $strUpdates";

        return $sql;
    }

    public function updateStatement($table, $updates, $where, $limit = 0x7fffffff) {
        $arrayUpdates = array();
        foreach ($updates as $upKey => $upValue) {
            $arrayUpdates[] = "`".$this->realEscapeString($upKey)."`='".$this->realEscapeString($upValue)."'";
        }
        $strUpdates = join(', ', $arrayUpdates);

        $sql = "UPDATE $table
                SET $strUpdates
                WHERE $where
                LIMIT ".intval($limit);

        return $sql;
    }

}

class DBMysqli {

    private $mysqli;

    private $host;
    private $username;
    private $password;
    private $dbname;
    private $port;

    function __construct($host, $username, $password, $dbname, $port = 3306) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->_connect($host, $username, $password, $dbname, $port);
    }

    protected function _connect($host, $username, $password, $dbname, $port) {
        $mysqli = new mysqli($host, $username, $password, $dbname, $port);
        if (!$mysqli) {
            trigger_error('connect db failed. error:'.$mysqli->errno);
        }
        $this->mysqli = $mysqli;
        return $this->mysqli;
    }

    protected function close() {
        $this->mysqli->close();
    }

    public function query($sql) {
        $retry = 3;
        while ($retry-- > 0) {
            $ret = $this->mysqli->query($sql);
            $errno = $this->mysqli->errno;
            if ($errno === 0) {
                return $ret;
            }

            if ($errno === 1062) {
                return $ret;
            }
            $this->_connect($this->host, $this->username, $this->password, $this->dbname, $this->port);
        }

        Trace::fatal('query failed. errno:'.$this->mysqli->errno.' error:'.$this->mysqli->error, __FILE__, __LINE__);
        trigger_error('query failed. errno:'.$this->mysqli->errno.' error:'.$this->mysqli->error);
    }

    public function realEscapeString($escapestr) {
        return $this->mysqli->real_escape_string($escapestr);
    }

    public function error() {
        return $this->mysqli->error;
    }

    public function errno() {
        return $this->mysqli->errno;
    }

    public function affectedRows() {
        return $this->mysqli->affected_rows;
    }

    public function insertID() {
        return $this->mysqli->insert_id;
    }

}
