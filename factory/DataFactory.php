<?php
namespace factory;

/**
 * Description of DataFactory
 *
 * @author Administrator
 */
class DataFactory {
    
    /**
     * 获取数据库对象实例
     * @return object   数据库对象实例
     */
    public static function getFactoryDrvier() {
        require PT . DS . 'config.php';
        $driver = null;
        switch ($data['type']) {
            case 'mysql':
            case 'mssql':
                $className = '\\drivers\\' . ucfirst($data['type']) . 'Driver';
                $driver = $className::getInstance();
                break;
            default :
                new FactoryException("Unkown SQL TYPE", 1, '');
                break;
        }
        return $driver;
    }
}

?>
