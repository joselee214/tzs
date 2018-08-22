<?php

//用于生成自动的daoMap时候自动方法扩展
/*
 * 即查询数据时候得到的 ActiveRecord对象的辅助文件，即 /class/daomap/j7data_***.class.php 文件
 * 该文件只能通过本配置文件进行简单的自定义方法，
 * 一般这个 AR 对象是通过 ->_field() 这样访问数据的
 *  但是我们经常需要根据本身的数据再次查询另外一个DAO获取另外一个数据。。。就可以通过这里指定，
 *  
 *  比如下面的 factory，定义了 factoryInfo，factoryBrand，categories，categories_arr
 *  通过php jsj.php d db=0 刷新之后，生成的 /class/daomap/j7data_factory.class.php 这个文件里，就自动关联了一些方法
 *  取到 j7data_factory 这个对象后，就可以简单的通过 ->_factoryInfo() 去访问关联数据
 */

//表数据的关联获取
$J7CONFIG['table_references'] = [];
//表对象,方法,参数$cond,$order
// ['table_references'][数据库连接别名][实际的表名]


//$J7CONFIG['table_references'][0]['order_goods'] = [
//    'refund'=>array('RefundService','getOrderRefundByOrderid',array('?orderid')),
//    'refunds'=>array('orderRefundDbDAO','get',array('order_id'=>'?orderid')),
//    'refund_status1'=>array('orderRefundDbDAO','get',array('order_id'=>'orderid','status'=>'1'),''),
//];