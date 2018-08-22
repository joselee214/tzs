


<style>
    body{background:#E8EEF7;}
</style>

<div style="margin: 5px 10px;">
    <div style="float: left;color: #1a76b7;font-weight: bold;">
        <?php
        foreach( $this->treemenu['menu'] as $em)
        {
            echo '<a target="treemenu" href="'.$this->rewrite_base.'/index/AdminIndex/ushow/treemenu/tp/'.$em['id'].'">'.$em['name'].'</a> |';
        }
        ?>

    </div>
    <div style="float: right;">
        set
        <a href="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/modulemenu/set/20">20</a>
        <a href="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/modulemenu/set/50">50</a>
        <a href="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/modulemenu/set/100">100</a>
        /Page
        &nbsp;&nbsp;
        <a target="mainframe" href="<?php echo $this->rewrite_base;?>/index/index/tp/logout">退出系统</a> |
        <a href="<?php echo $this->rewrite_base;?>/index/index/tp/editpassword" target="mainframe">密码修改</a>
    </div>
    <div style="clear: both;"></div>
</div>