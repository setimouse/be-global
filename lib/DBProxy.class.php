<?php
class DBProxy
{
    private static $queryNum = 0;
    private static $sqlLog = array();
    private static $timeSpan = 0;

    private static $stat = array();

    protected $conn;

    protected $db;

    public static function getConnection($db) {
        $proxy = new DBProxy($db);
        return $proxy;
    }

    function __construct($db = null) {
        $this->db = $db;
    }

    public function connect($host, $username, $password, $dbname, $port = 3306)
    {
        $mysqli = new mysqli($host, $username, $password, $dbname, $port);
        if ($mysqli) {
            $mysqli->query('SET NAMES UTF8');
        } else {
            trigger_error('connect db failed. error:'.$mysqli->errno);
        }

        return $mysqli;
    }

    public function connectDB($db, $rw = 'w') {
        DAssert::assert(in_array($rw, array('r', 'w')), 'illegal rw', __FILE__, __LINE__);

        $conf = Config::runtimeConfigForKeyPath('database.$.'.$db.'.'.$rw);

        $dbIndex = rand(0, count($conf['hosts']) - 1);
        $host = $conf['hosts'][$dbIndex]['h'];
        $port = $conf['hosts'][$dbIndex]['p'];
        $username = $conf['username'];
        $password = $conf['password'];
        $dbname = $conf['dbname'];

        $this->conn = $this->connect($host, $username, $password, $dbname, $port);
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }

    public function beginTransaction() {
        self::_doQuery('BEGIN', 'w');
    }

    public function endTransaction() {
        self::_doQuery('END', 'w');
    }

    public function commit() {
        self::_doQuery('COMMIT', 'w');
    }

    public function rollback() {
        self::_doQuery('ROLLBACK', 'w');
    }

    protected function _doQuery($sql, $rw)
    {
        self::$queryNum++;

        $startTime = microtime(true);
        $conn = $this->connectDB($this->db, $rw);
        $ret = $this->conn->query($sql);
        $endTime = microtime(true);

        if (MDict::D('is_debug')) {
            $timeSpan = $endTime - $startTime;
            self::$sqlLog[] = array('db' => $this, 'conn' => $conn, 'sql' => $sql, 'span' => sprintf("%.3fms", $timeSpan * 1000));
            self::$timeSpan+= $timeSpan;
        }

        if ($this->conn->errno !== 0) {
            Trace::fatal('query failed. errno:'.$this->conn->errno.' error:'.$this->conn->error, __FILE__, __LINE__);
        }
        return $ret;
    }

    public function doInsert($sql)
    {
        self::$stat['insert']++;
        return $this->_doQuery($sql, 'w');
    }

    public function doUpdate($sql)
    {
        self::$stat['update']++;
        return $this->_doQuery($sql, 'w');
    }

    public function doDelete($sql)
    {
        self::$stat['delete']++;
        return $this->_doQuery($sql, 'w');
    }

    public function doSelect($sql)
    {
        self::$stat['select']++;
        return $this->_doQuery($sql, 'r');
    }

    public function doRead($sql) {
        self::$stat['read']++;
        return $this->_doQuery($sql, 'r');
    }

    public function doWrite($sql) {
        self::$stat['write']++;
        return $this->_doQuery($sql, 'w');
    }

    public function affectedRows() {
        return $this->conn->affected_rows;
    }

    public function lastInsertID() {
        return $this->conn->insert_id;
    }

    public function lastError() {
        return $this->conn->error;
    }

    public function lastErrno() {
        return $this->conn->errno;
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

    public function rs2foundrows()
    {
        return $this->rs2firstvalue("SELECT FOUND_ROWS()");
    }

    public function realEscapeString($string)
    {
        return $this->conn->real_escape_string($string);
    }

    public static function getQueryNum()
    {
        return self::$queryNum;
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

    public static function getSqlLog()
    {
        return self::$sqlLog;
    }

    public static function getTimeSpan() {
        return self::$timeSpan;
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
            $arrayUpdates[] = "`".$this->realEscapeString($upKey)."`='".$dbProxy->realEscapeString($upValue)."'";
        }
        $strUpdates = join(', ', $arrayUpdates);

        $sql = "UPDATE $table
                SET $strUpdates
                WHERE $where
                LIMIT ".intval($limit);

        return $sql;
    }

}











