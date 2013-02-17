<?php
define('PT', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require PT . DS . 'util' . DS. 'ClassLoader.php';

$classLoader = new util\ClassLoader();
$classLoader->register();

$driver = factory\DataFactory::getFactoryDrvier();

//$data = array('zoneid'=>1, 'step'=>5, 'logtime'=>'12333', 'userid'=>'fweiefjiowj', 'ip'=>'127.0.0.1');
//新增数据
//echo $driver->insert('lanuch', $data);
//新增数据获取自增长ID
//echo $driver->insert('lanuch', $data, true);
//删除数据
//echo $driver->delete('lanuch', array('id'=>3));
//更新数据
//echo $driver->update('lanuch', array('step'=>3,'userid'=>'test'), array('id'=>2));
//查询数据
//print_r($driver->select('lanuch', array('id','userid'), null, array('id'=>'desc'), TRUE, 'id', 3, 15));

//$data = array('uname'=>'test', 'age'=>15);
//$driver->insert('users', $data);
//echo $driver->delete('users', array('id'=>'>=3'));
//echo $driver->update('users', array('uname'=>'test3'), array('uname'=>"='test1'"));

//print_r($driver->select('users', array('id', 'uname'), array('id'=>">'2'"), array('id'=>'desc'), TRUE, 0, 1));

//print_r($driver->query("select count(1) as num from users"));


//echo $driver->insert('users', array('uname'=>'test', 'age'=>13), TRUE);
//echo $driver->delete('users', array('id'=>">='5'"));
//echo $driver->update('users', array('uname'=>'test1'), array('id'=>"='1'"));
//print_r($driver->select('users', array('id', 'uname'), array('id'=>">=3"), array('id'=>'desc', 'uname'=>'asc'),
//        TRUE, 0, 1));


//$data = array('zoneid'=>1, 'step'=>5, 'logtime'=>'12333', 'userid'=>'fweiefjiowj', 'ip'=>'127.0.0.1');
//新增数据
//echo $driver->insert('lanuch', $data);
//新增数据获取自增长ID
//echo $driver->insert('lanuch', $data, true);
//echo $driver->delete('lanuch', array('id'=>" in (3,5,7)"));
//echo $driver->update('lanuch', array('zoneid'=>3, 'step'=>2), array('id'=>" in (8,9)"));
//print_r($driver->select('lanuch', array('id','userid'), null, array('id'=>'desc'), TRUE, 'id', 10, 15));
?>