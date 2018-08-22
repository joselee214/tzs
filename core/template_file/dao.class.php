<?php
echo '<?php'.PHP_EOL;
echo '/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 ';

if( $app )
    echo 'namespace j7f\\'.$app.';'.PHP_EOL;
?>

class j7data_<?php echo $_tmp_name;?> extends <?php echo $app?'\\':''; ?><?php echo $baseClass;?>

{

    protected $__data = [];
    protected $__data_keys = [<?php
    $cloums = [];
    foreach($_tmp_columns as $ecolum)
    {
        echo '\''.$ecolum['Field'].'\'=>null,';
    }
    echo '];'.PHP_EOL;?>

<?php
//$cloums = [];
//foreach($_tmp_columns as $ecolum)
//{
//    unset($ecolum['Privileges']);
//    $cloums[ $ecolum['Field'] ] = $ecolum;
//}
?>
<?php //    public $_txt_columns = <?php echo var_export($cloums,true); //str_replace(PHP_EOL,'',var_export($cloums,true));?>

<?php if( $_tmp_config )
    {
        foreach($_tmp_config as $k=>$e)
        {
            $args = $e[2];
            array_walk($args,array($this,'explainCond'));
            ?>

    function _<?php echo $k?>()
    {
        return call_user_func_array( [$this->getServiceDao('<?php echo $e[0]?>'),'<?php echo $e[1]?>'],[<?php echo PHP_EOL.'                '.implode(','.PHP_EOL.'                ',$args).PHP_EOL;?>            ] );
    }
            <?php
        }
    }
    ?>

    /*
    ** ActiveRecord 模式支持
    */

<?php
foreach($_tmp_columns as $ecolum)
{
    ?>
    /** @return $this */
    public function _<?php echo $ecolum['Field'];?>()
    {
        $args = func_get_args();
        array_unshift($args,'<?php echo $ecolum['Field'];?>');
        return $this->__property($args);
    }

<?php } ?>

}