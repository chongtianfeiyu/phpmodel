<?php

namespace drivers;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdoMysqlDriver
 *
 * @author Administrator
 */
class PdoMysqlDriver implements Driver {

    private static $instance;
    private $db;
    private $sql;

    /**
     * 获取单例对象
     * @return object   返回单例对象实例
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new PdoMysqlDriver();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
        global $data;
        $dsn = "mysql:host={$data['host']}";
        if ($data['port'] > 0) {
            $dsn .= ";port={$data['port']}";
        }
        $dsn .= ";dbname={$data['db']}";
        try {
            if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
                $dsn .= ";charset={$data['charset']}";
                $this->db = new \PDO($dsn, $data['user'], $data['pass']);
            } else {
                $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$data['charset']}",);
                $this->db = new \PDO($dsn, $data['user'], $data['pass'], $options);
            }
        } catch (\PDOException $e) {
            die('Could not connect mysql:' . $e->getMessage());
        }
    }

    /**
     * 执行插入操作
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param boolean $isGetId  是否获取自增长ID
     * @return $isGetID为TRUE返回ID，否则返回boolean
     */
    public function insert($table, $fields, $isGetId = FALSE) {
        $fieldStr = $valueStr = '';
        $isFirst = TRUE;
        foreach ($fields as $k => $v) {
            if ($isFirst) {
                $fieldStr .= $k;
                $valueStr .= "'{$v}'";
                $isFirst = FALSE;
            } else {
                $fieldStr .= ",{$k}";
                $valueStr .= ",'{$v}'";
            }
        }
        $this->sql = "insert into {$table}({$fieldStr}) values({$valueStr})";
        return $this->safeExec($isGetId);
    }

    /**
     * 删除数据操作
     * @param string $table 表名
     * @param array $where  条件数组
     * @return boolean  成功返回TRUE
     */
    public function delete($table, $where = NULL) {
        $wherStr = '';
        if ($where != NULL) {
            $isFirst = TRUE;
            foreach ($where as $k => $v) {
                if ($isFirst) {
                    $wherStr .= "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $wherStr .= " and {$k}{$v}";
                }
            }
        }
        $this->sql = "delete from {$table} {$wherStr}";
        return $this->safeExec();
    }
    
    /**
     * 更新数据操作
     * @param string $table 表名
     * @param array $fields 字段数组
     * @param array $where  条件数组
     * @return boolean  TRUE
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
                    $whereStr .= "where {$k}{$v}";
                    $isFirst = FALSE;
                } else {
                    $whereStr .= " and {$k}{$v}";
                }
            }
        }
        $this->sql = "update {$table} {$fieldStr} {$whereStr}";
        return $this->safeExec();
    }
    
    public function select($table, $fields=NULL, $where=NULL, 
            $order=NULL, $isPage=FALSE, $tops=0, $tope=0) {
        $fieldStr = '*';
        $whereStr = $orderStr = $limitStr = '';
        $isFirst = TRUE;
        if ($fields != NULL) {
            foreach ($fields as $k=>$v) {
                if ($isFirst) {
                    $fieldStr = $v;
                    $isFirst = FALSE;
                } else {
                    $fieldStr .= ",{$v}";
                }
            }
        }
        if ($where != NULL) {
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
        if ($order != NULL) {
            $isFirst = TRUE;
            foreach ($order as $k=>$v) {
                if ($isFirst) {
                    $orderStr = "order by {$k} {$v}";
                    $isFirst = FALSE;
                } else {
                    $orderStr .= ",{$k} {$v}";
                }
            }
        }
        if ($isPage) {
            $limitStr = "limit {$tops},{$tope}";
        }
        $this->sql = "select {$fieldStr} from {$table} {$whereStr} {$orderStr} {$limitStr}";
        return $this->getData();
    }

    /**
     * 执行安全操作
     * @param boolean $isGetId  是否获取自增长ID
     * @return $isGetId为TRUE返回ID，否则返回boolean
     */
    private function safeExec($isGetId = FALSE) {
        try {
            $this->db->beginTransaction();
            $this->db->exec($this->sql);
            if ($isGetId) {
                $id = $this->db->lastInsertId();
            }            
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            die($e->getMessage());
        }
        return ($isGetId) ? $id : TRUE;
    }
    
    /**
     * 获取查询数据
     * @return array    获取数据
     */
    private function getData() {
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
        unset($this->db);
    }
}
?>
