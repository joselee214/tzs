<div class='content-head'>
    权限组管理:
</div>
<style>
    #mytable{
         /* 表格边框 %%%%%%%%%%%%非常好的边框设置%%%%%%%%%*/
        font-family: Arial;
        border-collapse: collapse; /* 边框重叠 */
        background-color: #eaf5ff; /* 表格背景色  %%%%%%%%%%%%%%%%% 非常好的背景颜色 %%%%%%%%%%%%% */
        font-size: 14px;
        table-layout: fixed;
        word-wrap: break-word;
        word-break: break-all;
    }
    .deep{
        padding: 3px;
    }
    .deep0
    {
        float: left;
        margin: 10px;
        border: 1px solid #0058a3;
    }
</style>
<div  class='content-notice'>
        <?php if($this->action == 'default'){ ?>
            <div>
                <?php
                $this->_aplh->export();
                ?>
            </div>
        <?php }?>

        <?php if(in_array($this->action,array('editgroup','addgroup'))){ ?>
            <div class="content-notice2">
                <form action="<?php if(isset($this->groupinfo)) { ?><?php echo $this->rewrite_base;?>/index/AdminGroup/?action=updateGroup"<?php }else{ ?><?php echo $this->rewrite_base;?>/index/AdminGroup/?action=doGroup" <?php } ?> method="post">
                <div>
                    <input type="hidden" name="input[gid]" value="<?php echo isset($this->groupinfo)?$this->groupinfo['id']:'';?>">
                    分组名称<input type="text" name="input[group_name]" value="<?php if(isset($this->groupinfo)){ echo $this->groupinfo['title']; }?>" > <?php if(isset($this->groupinfo)){ ?><?php } ?><input type="submit" value="提交">
                </div>
                <div>
                 <?php
                 $gids= array();
                    if(isset($this->groupinfo))
                        $gids = explode(',',$this->groupinfo['cate_ids']);

                    function showmenu($d,$per=0,$gs)
                    {
                        $margin = 15*$per;
                        echo '<div id="f'.$d['id'].'" class="deep deep'.$per.'">';
                        echo '<div style="margin-left:'.$margin.'px;" id="'.$d['id'].'">'.'~'.$d['id'];
                        echo '<input type="checkbox" id="p_'.$d['id'].'" name="input[c_id][]" '.(in_array($d['id'],$gs)?'checked':'').' value="'.$d['id'].'">';
                        echo '<label for="p_'.$d['id'].'" style="color:#'.($d['is_show']?'000':'aaa').';">'.$d['name'].' '.$d['url'].'</label></div>';
                        if( isset($d['child']) )
                        {
                            foreach($d['child'] as $e)
                            {
                                showmenu($e,$per+1,$gs);
                            }
                        }
                        echo '</div>';
                    }

                    foreach($this->cateinfo as $em)
                    {
                        showmenu($em,0,$gids);
                    }

                    ?>
                    <div style="clear: both;"></div>
                </div>

                </form>
            </div>
        <?php } ?>
</div>


<script type="text/javascript">
    function select_channel_permission(id)
    {
        if($('#select_' + id).attr('checked') == 'checked'){
            $('input[id^="parent_' + id + '_"]').attr('checked', true);
        }else
            $('input[id^="parent_' + id + '_"]').attr('checked', false);
    }
    function onclick()
    {

    }
</script>
 