<?php
namespace drivers;

/**
 * Description of MysqlDriver
 *
 * @author Administrator
 */
class MysqlDriver implements Driver {
    private static $instance;
    private $db;
    private $sql;
    
    /**
     * 获取单例对象
     * @return object   数据库连接对象
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MysqlDriver();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        global $data;
        if ($data['port'] > 0) {
            $this->db = mysql_connect($data['host'].$data['port'], $data['user'], $data['pass']);
        } else {
            $this->db = mysql_connect($data['host'], $data['user'], $data['pass']);
        }        
        if (!$this->db) {
            die("Could not connect mysql: " . mysql_error());
        }
        if ($data['charset']) {
            mysql_set_charset($data['charset'], $this->db);
        }
        if (!mysql_select_db($data['db'], $this->db)) {
            die('Could not connect db: ' . mysql_error());
        }
    }
    
    /**
     * 插入数据
     * @param string $table 表名
     * @param array $fields 字段数组
     */
    public function insert($table, $fields) {
        $fieldStr = '';
        $valueStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $fieldStr .= mysql_escape_string($k);
                $valueStr .= "'" . mysql_escape_string($v) . "'";
                $isFirst = FALSE;
            } else {
                $fieldStr .= "," . mysql_escape_string($k);
                $valueStr .= ",'" . mysql_escape_string($v) . "'";
            }
        }
        $table = mysql_escape_string($table);
        $this->sql = "insert into {$table}({$fieldStr}) values({$valueStr})";
        return $this->saveExec();
    }
    
    /**
     * 删除数据
     * @param string $table 表名
     * @param array $where  条件数组
     */
    public function delete($table, $where=null) {
        $whereStr = '';
        if ($where != null) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                $k = mysql_escape_string($k);
                $v = mysql_escape_string($v);
                if ($isFirst) {
                    $whereStr .= "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}{$v}";
                }
            }
        }
        $table = mysql_escape_string($table);
        $this->sql = "delete from {$table} {$whereStr}";
        return $this->exec();
    }
    
    /**
     * 更新数据
     * @param string $table 表名
     * @param array $fields 更新内容数组
     * @param array $where  条件数组
     */
    public function update($table, $fields, $where=null) {
        $fieldStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $fieldStr .= "{$k}='{$v}'";
                $isFirst = FALSE;
            } else {
                $fieldStr .= ",{$k}='{$v}'";
            }
        }
        $whereStr = '';
        if ($where != null) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                if ($isFirst) {
                    $whereStr .= "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}{$v}";
                }
            }
        }
        $table = mysql_escape_string($table);
        $this->sql = "update {$table} set {$fieldStr} {$whereStr}";
        return $this->exec();
    }
    
    /**
     * 查询数据
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param array $where  条件数组
     * @param array $order  排序数组
     * @param boolean $isPage   是否分页
     * @param int $start    开始数值
     * @param int $end  结束数值
     * @return array    数组数据
     */
    public function select($table, $fields=null, $where=null, $order=null, $isPage=FALSE, $start=0, $end=0) {
        $fieldStr = '*';
        if ($fields != null) {
            $fieldStr = '';
            $isFirst = TRUE;
            foreach ($fields as $k=>$v) {
                if ($isFirst) {
                    $fieldStr .= $v;
                    $isFirst = FALSE;
                } else {
                    $fieldStr .= ",{$v}";
                }
            }
        }
        $whereStr = '';
        if ($where != null) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                if ($isFirst) {
                    $whereStr .= "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}{$v}";
                }
            }
        }
        $orderStr = '';
        if ($order != null) {
            $isFirst = TRUE;
            foreach ($order as $k=>$v) {
                if ($isFirst) {
                    $orderStr .= "order by {$k} {$v}";
                    $isFirst = FALSE;
                } else {
                    $orderStr .= ",{$k} {$v}";
                }
            }
        }
        $limitStr = '';
        if ($isPage) {
            $limitStr .= "limit {$start},{$end}";
        }
        $this->sql = "select {$fieldStr} from {$table} {$whereStr} {$orderStr} {$limitStr}";
        return $this->selectExec();
    }
    
    /**
     * 使用sql查询
     * @param string $sql   sql语句
     */
    public function query($sql) {
        $this->sql = $sql;
        if (stripos($this->sql, 'select') === 0) {
            return $this->selectExec();
        } else if (stripos($this->sql, 'insert') === 0) {
            return $this->saveExec();
        } else {
            return $this->exec();
        }
    }

        /**
     * 插入数据执行
     */
    private function saveExec() {
        $query = mysql_query($this->sql, $this->db);
        if (!$query) {
            die(mysql_error());
        }
        return mysql_insert_id();
    }
    
    /**
     * 执行语句
     */
    private function exec() {
        if (mysql_query($this->sql, $this->db)) {
            return TRUE;
        } else {
            die(mysql_error());
        }        
    }
    
    /**
     * 执行查询语句
     */
    private function selectExec() {
        $data = array();
        $query = mysql_query($this->sql, $this->db);
        if (!$query) {
            die(mysql_error());
        }
        while ($rs = mysql_fetch_assoc($query)) {
            $data[] = $rs;
        }
        mysql_free_result($query);
        return $data;
    }
    
    /**
     * 析构函数
     */
    public function __destruct() {
        mysql_close($this->db);
    }
}

?>
