<?php echo '<?php';?>

//require_once __DIR__.'/<?php echo $param['folder_rootclass'];?>.php';

class <?php echo $param['folder_rootapp'];?>_<?php echo $param['folder_name'];?>_common extends <?php echo $param['folder_rootclass'];?>

{
    //Action 加载时候执行的方法...
    public function __j7construct()
    {
        parent::__j7construct();
    }
}