<?php echo '<?php';?>

class <?php echo $param['action_app'];?>_<?php echo $param['action_folder'];?>_<?php echo $param['action_name'];?>_Action extends <?php echo $param['action_app'];?>_<?php echo $param['action_folder'];?>_common
{

    public function execute()
    {
<?php
if( in_array('json',explode(',',$param['action_return']))  )
{
    ?>
        $this->_ret = ['_test'=>1];
        $this->_setResultType('json');
<?php
}
elseif( in_array('jsonp',explode(',',$param['action_return']))  )
{
    ?>
        $this->callback = 'test_callback';
        $this->_ret = ['_test'=>1];
        $this->_setResultType('jsonp');
<?php
}
?>
    }

}