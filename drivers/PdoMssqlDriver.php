<?php
namespace drivers;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdoMssqlDriver
 *
 * @author Administrator
 */
class PdoMssqlDriver implements Driver {
    private static $instance;
    private $db;
    private $sql;
    
    /**
     * 获取单例实例
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new PdoMssqlDriver();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        global $data;
        $dsn = "sqlsrv:Server={$data['host']}";
        if ($data['port'] > 0) {
            $dsn .= ",{$data['port']}";
        }
        $dsn .= ";Database={$data['db']}";
        try {
            $this->db = new \PDO($dsn, $data['user'], $data['pass']);
        } catch (\PDOException $e) {
            die('Could not connect mssql:' . $e->getMessage());
        }
        $this->db->query("set names {$data['charset']}");
    }
    
    /**
     * 插入数据
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param boolean $isGetId  是否获取ID
     * @return type
     */
    public function insert($table, $fields, $isGetId=FALSE) {
        $fieldStr = $valueStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $fieldStr .= $k;
                $valueStr .= "'{$v}'";
                $isFirst = FALSE;
            } else {
                $fieldStr .= ",{$k}";
                $valueStr .= ",'{$v}'";
            }
        }
        $this->sql = "insert {$table}($fieldStr) values($valueStr)";
        return $isGetId ? $this->safeIdExec() : $this->safeExec();
    }
    
    /**
     * 删除数据
     * @param string $table 表名
     * @param array $where  条件数组
     * @return boolean  成功返回TRUE
     */
    public function delete($table, $where=NULL) {
        $whereStr = '';
        if ($where != NULL) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                if ($isFirst) {
                    $whereStr = "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}{$v}";
                }
            }
        }
        $this->sql = "delete from {$table} {$whereStr}";
        return $this->safeExec();
    }
    
    /**
     * 更新数据
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param array $where  条件数组
     * @return boolean
     */
    public function update($table, $fields, $where=NULL) {
        $fieldStr = $whereStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k=>$v) {
            if ($isFirst) {
                $fieldStr .= "set {$k}='{$v}'";
                $isFirst = FALSE;
            } else {
                $fieldStr .= ",{$k}='{$v}'";
            }
        }
        if ($where != NULL) {
            $isFirst = TRUE;
            foreach ($where as $k=>$v) {
                if ($isFirst) {
                    $whereStr = "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr = " and {$k}{$v}";
                }
            }
        }
        $this->sql = "update {$table} {$fieldStr} {$whereStr}";
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
     * 执行插入语句
     * @return int  子增ID
     */
    private function safeIdExec() {
        try {
            $this->db->beginTransaction();
            $this->db->exec($this->sql);
            $stmt = $this->db->query("select lastid=@@IDENTITY");
            $id = 0;
            while ($rs = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $id = $rs['lastid'];
            }
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            die('Error:' . $e->getMessage());
        }
        return $id;
    }
    
    /**
     * 执行语句
     * @return boolean TRUE
     */
    private function safeExec() {
        try {
            $this->db->beginTransaction();
            $this->db->exec($this->sql);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            die('Error:' . $e->getMessage());
        }
        return true;
    }
    
    /**
     * 执行语句
     * @return array
     */
    private function exec() {
        $stmt = $this->db->query($this->sql);
        $data = array();
        while ($rs = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $rs;
        }
        return $data;
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        $this->db = NULL;
    }
}
?>
