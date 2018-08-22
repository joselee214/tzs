<?php
$this->title = 'UnitTest';
?>


    <div class='content-head'>
        <div>
            UnitTest 结果报表
        </div>
        <div style="font-size: 12px;margin: 5px 20px 10px;">
            此报表结构为参考
        </div>
    </div>

    <style type="text/css">
        .hname{ font-size: 20px;font-weight: bold; }
        .hname2{}
        .hnamered{color: red;}
    </style>

    <?php foreach($this->vdata as $vdata){ ?>
    <div class="content-notice">
        <div>
        测试类 <span class="hname"><?php echo $vdata['className'];?></span> :: 调用方法 <span class="hname"><?php echo $vdata['method'];?></span></div>
        <div class="content-notice2">
            <div>
                类实例化 : <span class="hname2"><?php var_export($vdata['classp']);?></span>
            </div>
            <div>
                方法参数 : <span class="hname2"><?php var_export($vdata['callp']);?></span>
            </div>
        </div>
        <div class="content-notice2">
            <div style="float: left;margin: 0 20px 0 10px;">
                <?php if($vdata['Assert']){ ?>
                <span class="hname">True</span>
                <?php }else{ ?>
                <span class="hname hnamered">False</span>
                <?php } ?>
            </div>
            <div style="float: left;width: 1500px;">
                <?php if(isset($vdata['msg'])&&$vdata['msg']){
                foreach($vdata['msg'] as $emsg)
                {
                    echo '<div>'.$emsg.'</div>';
                }
                }?>
                <div class="content-notice">
                <?php if(isset($vdata['msgtest'])&&$vdata['msgtest']){
                foreach($vdata['msgtest'] as $emsg)
                {
                    echo '<pre>'.$emsg.'</pre>';
                }
                }?>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <?php } ?>
