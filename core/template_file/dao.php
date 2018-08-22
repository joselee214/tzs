<?php echo '<?php';?>

<?php if(isset($param['dao_rootapp']) && $param['dao_rootapp'] ){
    if( strpos($param['dao_rootclass'],'Redis')!==false ){ ?>
        require_once J7SYS_DAO_DIR.'/<?php echo $param['dao_rootclass'];?>.php';

    <?php } ?>
namespace j7f\<?php echo $param['dao_rootapp'];?>;
class <?php echo $param['dao_name'].$param['dao_extname'];?> extends \<?php echo $param['dao_rootclass'];?>
<?php }else{ ?>
class <?php echo $param['dao_name'].$param['dao_extname'];?> extends <?php echo $param['dao_rootclass'];?>
<?php } ?>

{

<?php
if( $param['dao_rootclass']!='DAO' ) {
if( $param['dao_tablename'] )
{
    $__tablename = $param['dao_tablename'];?>
    //表名
    protected $_table = '<?php echo $param['dao_tablename'];?>';
    <?php
}
else
{
    $__tablename = $param['dao_name']; ?>
    //表名
    //protected $_table = '<?php echo $param['dao_name'];?>';
    <?php
}
}

if( isset($param['dao_db']) ) {

    ?>

    //数据库链接id
    protected $_dbindex = <?php echo is_numeric($param['dao_db']) ? $param['dao_db'] : '\'' . $param['dao_db'] . '\''; ?>;

    //AR对象名
<?php
if (isset($mparam['map']) && $mparam['map']) {
    $__map = $mparam['map']; ?>
    protected $_data_map = '<?php echo $mparam['map']; ?>';
    <?php
} elseif (isset($param['map']) && $param['map']) {
    $__map = $param['map']; ?>
    //protected $_data_map = '<?php echo $param['map']; ?>';
    <?php
} else {
    $__map = $param['dao_tablename']; ?>
    //protected $_data_map = '<?php echo $param['dao_tablename']; ?>';
    <?php
}
if (isset($param['dao_rootapp']) && $param['dao_rootapp']) {
    if (file_exists(J7Config::instance()->getAppDir($param['dao_rootapp']) . '/dao/map/j7data_' . $__map . '.class.php'))
        $__map = '\\j7f\\' . $param['dao_rootapp'] . '\\j7data_' . $__map;
    else
        $__map = '\\j7data_' . $__map;
} else
    $__map = 'j7data_' . $__map;
?>

<?php
if (count($param['dao_pks']) > 1) { ?>
    protected $_pk = array('<?php echo implode("','", $param['dao_pks']); ?>');   //主键
<?php } else { ?>
    protected $_pk = '<?php echo $param['dao_pkname']; ?>';   //主键
<?php } ?>


<?php
if ($param['dao_rootclass'] == 'RedisCrudDAO') { ?>
    //存储主键,必须有! 单主键，[多主键注意前后顺序] ,必须是唯一确定。。。//protected $_pk = array('c1','c2') ;
    //以下两个设置是查询结构的设置，包括delete，update里面的查询结构
    //注意不要重复，inset 与 inzset 里的cond 是一致的，不用重新设
    //inzset 里的 order 是排序字段，在使用的时候是跟着cond条件定位的
    protected $_pk_inset = array(array('c2'),array('c3','c4'));
    protected $_pk_inzset = array( array('cond'=>array('c4'),'order'=>'c7'),array('cond'=>array('c5','c3'),'order'=>'c6')); //

    //数据过期时间，这个时间是针对所有涉及到修改动作的数据的过期时间，设为0即不过期
    protected $_expiretime = 1296000;//15*24*3600;

    <?php
} elseif ($param['dao_rootclass'] == 'CacheRedisDbCrudDAO' || $param['dao_rootclass'] == 'RedisSimpleDAO') {
    ?>

    //主键,能定位到唯一记录的主键
    protected $_pk_store = array('sUid','iHeroId');     //存储主键
    protected $_pk_dbset = 'uid';              //分表或分库依赖键，目前系统默认为uid

    <?php
} elseif ($param['dao_rootclass'] == 'DbCrudDAO') {
    /**    ?>
     *
     * //可能需要手动调用方法进行分表或者分库...
     *
     * <?php
     * */
}
?>

<?php if (strpos($param['dao_rootclass'], 'Redis') === false) { ?>

    /*
    ************************
    * new方法是生成activeRecord的对象,临时数据,保存需要 $obj->save()
    */

    /** @return <?php echo $__map; ?> */
    public function _new($data=[])
    {
        $obj = new <?php echo $__map; ?>(get_called_class(),true);
        $obj->fromArray($data);
        return $obj;
    }

    /*
    ************************
    * 以下接口主要是为了做代码提示
    */

    /** @return <?php echo $__map; ?>|null */
    public function getByPk($pk,$pinfo = [])
    {
        return parent::getByPk($pk,$pinfo);
    }

    /** @return <?php echo $__map; ?>|null */
    public function getByFk($cond, $pinfo = [])
    {
        return parent::getByFk($cond,$pinfo);
    }

    /** @return <?php echo $__map; ?>[]|null */
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::get($cond,$order,$count,$offset,$cols, $returnAr);
    }

    /** @return <?php echo $__map; ?>|null */
    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::getOne($cond,$order,$count,$offset,$cols, $returnAr);
    }

<?php }
}?>

}
