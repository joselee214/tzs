<?php echo '<?php';?>

<?php if(isset($param['service_rootapp']) && $param['service_rootapp'] ){ ?>
namespace j7f\<?php echo $param['service_rootapp'];?>;
require_once J7SYS_CLASS_DIR.'/basichelp/<?php echo $param['service_rootapp'];?>_basic_service.php';
class <?php echo $param['service_name'];?>Service extends \<?php echo $param['service_rootapp'];?>_basic_service
<?php }else{ ?>
class <?php echo $param['service_name'];?>Service extends basic_service
<?php } ?>
{
    <?php
        if(isset($param['service_dao_split']) && $param['service_dao_split'])
{
    foreach($param['service_dao_split'] as $es)
    {
    if($es)
    {
        $esh = $es;
        if( isset($param['service_rootapp']) && $param['service_rootapp'] && file_exists( J7Config::instance()->getAppDir($param['service_rootapp']).'/dao/'.$es.'.php' ) )
            $esh = '\\j7f\\'.$param['service_rootapp'].'\\'.$es;
        ?>

    /**
     * @var <?php echo $esh;?>

     */
    protected $<?php echo $es;?>;
<?php
    }
    }
}
?>


    public function test($test)
    {
        return 'test:'.$test;
    }
}
