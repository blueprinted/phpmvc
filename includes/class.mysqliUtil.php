<?php
/**
 *  mysqliUtil 操作类(单列)
 *
 */
class mysqliUtil
{
    public $unit;
    public $link;//当前数据库连接标识
    public $dbname;
    public $querynum = 0;
    private static $instance = array();
    private function __construct($unit)
    {
        $this->unit = $unit;
    }
    private function __clone()
    {
    }
    public static function getInstance($unit)
    {
        if (!isset(self::$instance[$unit]) || !(self::$instance[$unit] instanceof self)) {
            self::$instance[$unit] = new self($unit);
        }
        return self::$instance[$unit];
    }
    /**
     *  @return Array
     */
    protected function get_conf()
    {
        $args = func_get_args();
        $conf = @array_shift($args);
        foreach ($args as $k) {
            if (is_array($conf) && isset($conf[$k])) {
                $conf = $conf[$k];
            } else {
                $conf = null;
                break;
            }
        }
        return is_array($conf) ? $conf : array();
    }
    public function connect($config)
    {
        if (!is_array($config)) {
            return false;
        }
        $dbhost = isset($config['host']) ? $config['host'] : 'localhost';
        $dbport = isset($config['port']) ? $config['port'] : '3306';
        $dbuser = $config['user'];
        $dbpass = $config['pass'];
        $dbname = isset($config['dbname']) ? $config['dbname'] : '';
        $charset = isset($config['charset']) ? $config['charset'] : 'utf8';
        $pconnect = isset($config['pconnect']) ? $this->config['pconnect'] : 0;
        $halt = isset($this->config['halt']) ? $this->config['halt'] : !0;
        if ($pconnect) {
            if (!$this->link = mysqli_pconnect($dbhost, $dbuser, $dbpass)) {
                $halt && $this->halt('Can not connect to MySQL server');
            }
        } else {
            if (!$this->link = mysqli_connect($dbhost, $dbuser, $dbpass)) {
                $halt && $this->halt('Can not connect to MySQL server');
            }
        }
        if ($dbname) {
            if (mysqli_select_db($this->link, $dbname)) {
                $this->dbname = $dbname;
            }
        }
        if ($charset) {
            mysqli_query($this->link, "SET NAMES {$charset}");
        }
        return $this->link;
    }
    //mysqli重连 返回连接标识
	public function reconnect() {
		if ($this->link) {
			$this->close();
			return $this->init_connect();
		}
		return null;
	}
    public function init_connect()
    {
        global $_DBCFG;
        if (!$this->link) {
            $this->connect($this->get_conf($_DBCFG, $this->unit));
        }
        return $this->link ? true : false;
    }
    public function select_db($dbname)
    {
        return $this->init_connect() ? (mysqli_select_db($dbname, $this->link)?(boolean)($this->dbname=$dbname):false) : false;
    }
    public function fetch_array($query, $result_type = MYSQLI_ASSOC)
    {
        return mysqli_fetch_array($query, $result_type);
    }
    public function query($sql, $type = '')
    {
        if (!$this->init_connect()) {
            return false;
        }
        $func = $type == 'UNBUFFERED' && @function_exists('mysqli_unbuffered_query') ?
            'mysqli_unbuffered_query' : 'mysqli_query';
        if (!($query = $func($this->link, $sql)) && $type != 'SILENT') {
            $this->halt('MySQL Query Error', $sql);
        }
        $this->querynum++;
        return $query;
    }
    /** 防止 sql 注入的sql执行方法 可以执行各类型的sql操作
     * @param $sql 需要预处理的sql
     * @param $types String  i:整型  d:double  s:string  b:blob 这几个字符的组合 与 ...$args对应的数据字段的类型对应
     * @param ...$args 多参数
     * @return Boolean / resuorce true/false
     * @mysqli_stmt_bind_param()
     */
    public function stmt_query($sql, $types, ...$args)
    {
        if (!$this->init_connect()) {
            return false;
        }
        //mysqli_prepare() returns a statement object or FALSE if an error occurred.
        if (false === ($stmt = mysqli_prepare($this->link, $sql))) {
            return false;
        }
        if (!mysqli_stmt_bind_param($stmt, $types, ...$args)) { //该函数的参数是引用传递 Returns TRUE on success or FALSE on failure.//该函数的参数是引用传递 Returns TRUE on success or FALSE on failure.
            return false;
        }
        $re = mysqli_stmt_execute($stmt); //成功时返回 TRUE 或者在失败时返回 FALSE。
	    $this->querynum++;
        if (strtolower(substr($sql, 0, 6)) != 'select') {
            mysqli_stmt_close($stmt);
            return $re;
        }
        $re = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $re;
    }
    public function affected_rows()
    {
        return $this->init_connect() ? mysqli_affected_rows($this->link) : -1;
    }
    public function error()
    {
        return $this->link ? mysqli_error($this->link) : '';
    }
    public function errno()
    {
        return $this->link ? mysqli_errno($this->link) : 0;
    }
    public function result($query, $row, $field = 0)
    {
        $query->data_seek($row);
        $array = mysqli_fetch_array($query, MYSQLI_NUM);
        return isset($array[$field]) ? $array[$field] : null;
    }
    public function num_rows($query)
    {
        $query = mysqli_num_rows($query);
        return $query;
    }
    public function num_fields($query)
    {
        return mysqli_num_fields($query);
    }
    public function free_result($query)
    {
        return mysqli_free_result($query);
    }
    public function insert_id()
    {
        return $this->init_connect() ? (($id = mysqli_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0)) : 0;
    }
    public function fetch_row($query)
    {
        return mysqli_fetch_row($query);
    }
    public function fetch_fields($query)
    {
        return mysqli_fetch_field($query);
    }
    public function fetch_all($query, $result_type = MYSQLI_ASSOC)
    {
        $rows = array();
        if (!$this->init_connect()) {
            return $rows;
        }
        while ($row = mysqli_fetch_array($query, $result_type)) {
            $rows[] = $row;
        }
        return $rows;
    }
    /** 功能 更新数据表数据
     *  @param $table String 表名 必须
     *  @param $data Array 要更新的数据 如array('field1'=>$value1,'field2'=>$value2, ...)
     *  @param $condition String 条件 如"`uid`=1"
     *  @param $limit 限制更新条数 [true:是, false:否]
     *  @return Integer
            1:更新成功
            0:更新失败
            -1:sql语句为空
     */
    public function update_table($table, $data, $condition, $limit = false)
    {
        $sql = self::parse_update_sql($table, $data, $condition, $limit);
        if (empty($sql)) {
            return -1;
        }
        return $this->query($sql) ? 1 : 0;
    }
    /** 功能 写入数据至表
     *  @param $table String 表名 必须
     *  @param $data Array 要写入的数据 如array('field1'=>$value1,'field2'=>$value2, ...)
     *  @param $return_insert_id Boolean 写入成功是否返回主键id(数字) [true:是, false:否]
     *  @param $replace Boolean 是否使用replace [true:是,false:否] 缺省false
     *  @return Integer
            自增ID:写入成功
            1:写入成功
            0:写入失败
            -1:sql语句为空
     */
    public function insert_table($table, $data, $return_insert_id = true, $replace = false)
    {
        $sql = self::parse_insert_sql($table, $data, $replace);
        if (empty($sql)) {
            return -1;
        }
        return $this->query($sql) ? ($return_insert_id ? $this->insert_id() : 1) : 0;
    }
    public function version()
    {
        return $this->init_connect() ? mysqli_get_server_info($this->link) : mysqli_get_server_info();
    }
    public function close()
    {
        return $this->init_connect() ? (mysqli_close($this->link) && $this->link = null) : false;
    }
    public function get_querynum()
    {
        return $this->querynum;
    }
    public function halt($message = '', $sql = '')
    {
        $dberror = $this->error();
        $dberrno = $this->errno();
        $info = "<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">";
        $info .= "<b>MySQL Error</b><br>";
        $info .= "<b>Message</b>: $message<br>";
        if (true) { //脚本环境下开启详情
            $info .= "<b>SQL</b>: $sql<br>";
            $info .= "<b>Error</b>: $dberror<br>";
        }
        $info .= "<b>Errno.</b>: $dberrno<br>";
        $info .= "</div>";
        echo $info;
        exit();
    }
  /**   功能 生成更新sql语句
   *    @param $table String 表名
   *    @param $data Array 如array('field1'=>$value1,'field2'=>$value2, ...)
   *    @param $condition String 条件 如"`uid`=1"
   *    @param $limit 限制更新条数 缺省false[true:是, false:否]
   *    @return String
   */
    public static function parse_update_sql($table, $data, $condition, $limit = false)
    {
        $sql = $comma = '';
        if (is_array($data) && $data) {
            $sql = "UPDATE `{$table}` SET ";
            foreach ($data as $key => $value) {
                $sql .= "{$comma}`{$key}`='{$value}'";
                $comma = ',';
            }
            $sql .= " WHERE ".$condition;
            if ($limit) {
                $sql .= " LIMIT 1";
            }
        }
        return $sql;
    }
  /**   功能 生成插入sql语句
   *    @param $table String 表名
   *    @param $data Array 如array('field1'=>$value1,'field2'=>$value2, ...)
   *    @param $replace Boolean 是否使用replace [true:是,false:否] 缺省false
   *    @return String
   */
    public static function parse_insert_sql($table, $data, $replace = false)
    {
        $sql_head = $sql = $comma = '';
        if (is_array($data) && $data) {
            $sql_head .= ($replace?"REPLACE":"INSERT")." INTO `{$table}`(";
            $sql .= "VALUES(";
            foreach ($data as $key => $value) {
                $sql_head .= "{$comma}`{$key}`";
                $sql .= "{$comma}'{$value}'";
                $comma = ',';
            }
            $sql_head .= ")";
            $sql .= ")";
            $sql = $sql_head . " " . $sql;
        }
        return $sql;
    }
    /*
	 * ping 连接
     * 原生mysqli_ping()返回boolean
	 * */
	public function ping(){
        return $this->init_connect() ? (@mysqli_ping($this->link)) : false;
    }
}
