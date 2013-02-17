<?php
namespace drivers;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MssqlDriver
 *
 * @author Administrator
 */
class MssqlDriver implements Driver {
    private static $instance;
    private $db;
    private $sql;
    
    /**
     * 获取单例对象
     * @return object   数据库连接对象
     */    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MssqlDriver();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        global $data;
        if ($data['port'] > 0) {
            $servername = "{$data['host']}, {$data['port']}";
        } else {
            $servername = "{$data['host']}";
        }
        $connInfo = array('CharacterSet' => $data['charset'],
                'Database' => $data['db'],
                'UID' => $data['user'],
                'PWD' => $data['pass'],
            );
        $this->db = sqlsrv_connect($servername, $connInfo);
        if (!$this->db) {
            die('Could not connect mssql: ' . sqlsrv_errors());
        }
    }
    
    /**
     * 执行查询语句
     */
    private function exec() {
        echo $this->sql;
        $data = array();
        $query = sqlsrv_query($this->db, $this->sql);
        if (!$query) {
            die(print_r(sqlsrv_errors(), true));
        }
        while ($rs = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $data[] = $rs;
        }
        sqlsrv_free_stmt($query);
        return $data;
    }

    /**
     * 安全执行SQL
     */
    private function safeExec() {
        if (sqlsrv_begin_transaction($this->db) === FALSE) {
            die(print_r(sqlsrv_errors(), true));
        }
        $stmt = sqlsrv_query($this->db, $this->sql);
        if ($stmt) {
            sqlsrv_commit($this->db);
            return $stmt;
        } else {
            sqlsrv_roollback($this->db);
            die(print_r(sqlsrv_errors(), true));
        }
    }
    
    /**
     * 插入数据获取自增长ID
     */
    private function safeIdExec() {
        if (sqlsrv_begin_transaction($this->db) === FALSE) {
            die(print_r(sqlsrv_errors(), true));
        }
        $stmt1 = sqlsrv_query($this->db, $this->sql);
        $stmt2 = sqlsrv_query($this->db, 'select lastid=@@IDENTITY');
        $id = 0;
        while ($rs = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
            $id = $rs['lastid'];
        }
        if ($stmt1 && $stmt2) {
            sqlsrv_commit($this->db);
            return $id;
        } else {
            sqlsrv_roollback($this->db);
            die(print_r(sqlsrv_errors(), true));
        }        
    }
    
    /**
     * 插入数据
     * @param string $table 表名
     * @param array $fields 字段数值
     * @param boolean $isGetId 是否获取ID
     * @return boolean  TRUE
     */
    public function insert($table, $fields, $isGetId=FALSE) {
        $fieldStr = $valueStr = '';
        $isFirst = true;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $fieldStr .= "{$k}";
                $valueStr .= "'{$v}'";
                $isFirst = false;
            } else {
                $fieldStr .= ",{$k}";
                $valueStr .= ",'{$v}'";
            }
        }
        $this->sql = "insert into {$table}({$fieldStr}) values({$valueStr})";
        if ($isGetId) {
            return $this->safeIdExec();
        } else {
            return $this->safeExec();
        }
    }
    
    /**
     * 删除数据
     * @param string $table 表名
     * @param array $where  条件数据
     */
    public function delete($table, $where) {
        $whereStr = '';
        $isFirst = true;
        foreach ($where as $k=>$v) {
            if ($isFirst) {
                $whereStr .= "where {$k}='{$v}'";
                $isFirst = FALSE;
            } else {
                $whereStr .= " and {$k}='{$v}'";
            }
        }
        $this->sql = "delete from {$table} {$whereStr}";
        return $this->safeExec();
    }
    
    /**
     * 更新数据
     * @param string $table
     * @param array $fields
     * @param array $where
     */
    public function update($table, $fields, $where=null) {
        $updateStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $updateStr .= "{$k}='{$v}'";
                $isFirst = FALSE;                
            } else {
                $updateStr .= ",{$k}='{$v}'";
            }
        }
        $whereStr = '';
        if ($where != NULL) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                if ($isFirst) {
                    $whereStr .= "where {$k}='{$v}'";
                    $isFirst = FALSE;                    
                } else {
                    $whereStr .= " and {$k}='{$v}'";
                }
            }            
        }
        $this->sql = "update {$table} set {$updateStr} {$whereStr}";
        return $this->safeExec();
    }
    
    /**
     * 查询数据
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param array $where  查询数组
     * @param array $order  排序数组
     * @param boolean $isPage   是否分组
     * @param int $tops 开始数值
     * @param int $tope 结束数值
     */
    public function select($table, $fields=null, $where=null, 
            $order=null, $isPage=FALSE, $key=null, $tops=0, $tope=0) {
        $fieldStr = '*';
        if ($fields != null) {
            $fieldStr = '';
            $isFirst = TRUE;
            foreach ($fields as $k=>$v) {
                if ($isFirst) {
                    $fieldStr .= "{$v}";
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
                    $whereStr .= "where {$k}='{$v}'";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}='{$v}'";
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
        if ($isPage) {            
            $this->sql = "select a.{$fieldStr} from {$table} a,(select top {$tope} row_number() over ({$orderStr}) n, {$key} from {$table} 
                {$whereStr}) b where a.{$key}=b.{$key} and b.n>{$tops} order by b.n asc";
        } else {
            $this->sql = "select {$fieldStr} from {$table} {$whereStr} {$orderStr}";
        }
        return $this->exec();
    }
    
    /**
     * 执行SQL语句
     * @param string $sql   sql语句
     */
    public function query($sql) {
        $this->sql = $sql;
        if (stripos($this->sql, 'select') === 0) {
            return $this->exec();
        } else if (stripos($this->sql, 'insert') === 0) {
            return $this->safeIdExec();
        } else {
            return $this->safeExec();
        }        
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        sqlsrv_close($this->db);
    }
}

?>