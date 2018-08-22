
    <div class='content-head'>
        <div>
            FileTemplate 创建文件:
        </div>
        <div style="font-size: 12px;margin: 5px 20px 10px;">
            仅在DEV上实现，主要是辅助phper，创建文件而已；有一定局限性，注意使用；注意各个参数大小写
        </div>
    </div>

    <style type="text/css">
        .showpanel{}
        .showpanel div{margin: 5px;}
        div.flssord{float: left;width: 150px;overflow: hidden;word-break: break-all;white-space:nowrap;margin: 2px;}
    </style>
    <script type="text/javascript">
        var cmdpath = "<?php echo $this->rewrite_base;?>" + "/index/FileTemplate/?";
        function submitinfo(todid,dataids)
        {
            console.log(todid);
            $('#'+todid).hide();
            var tourl = cmdpath+'act='+todid;
            $.each( dataids.split(','), function(index, value) {
                log(value);
                if( $('#'+value).get(0).tagName == 'DIV' )
                {
                    tourl = tourl+'&param['+value+']=';
                    $('#'+value+' input:checkbox[name="'+value+'"]:checked').each(function(i,v){
                            tourl = tourl+$(this).val()+',';
                        });
                }
                else
                {
                    tourl = tourl+'&param['+value+']='+$('#'+value).val();
                }
            } );
            log(tourl);
            $.getJSON( tourl+"&callback=?" , function(req){
                $('#'+todid).html('');
                 $.each( req.msg , function(index, value) {
                     str = $('#'+todid).html();
                     str += value+'<br/>';
                     $('#'+todid).html(str);
                     $('#'+todid).show();
                     log(value);
                 });
            });
        }
    </script>

    <div class="content-notice">
        <div>
            Action: ( core/tmp/action.php ) <a href="#" onclick="window.location.reload();" style="color: blue;">刷新本页</a>
        </div>
        <div class="content-notice2 showpanel">
            <div>
                App:
                <select id='action_app' onchange="location.href='?select[action]='+this.options[this.selectedIndex].value;">
                    <?php
                    foreach($this->data['allapps'] as $ecc)
                    {
                        if( isset($this->select['action']) && $this->select['action']==$ecc )
                            echo '<option value="'.$ecc.'" selected>'.$ecc.'</option>';
                        else
                            echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select>
                Module:
                <select id='action_folder'>
                    <?php
                    foreach($this->data['appfolder'] as $ecc)
                    {
                        echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select>
                , Action_Name:<input id='action_name' style="text-align: right;">Action
            </div>
            <div id='action_service'>
                使用的Service文件: (app私有service)
                <div>
                    <?php
                    if( isset($this->data['appservice']) )
                    {
                        foreach($this->data['appservice'] as $ecc)
                        {
                            echo '<div class="flssord"><input type="checkbox" id="action_service_'.$ecc.'" name="action_service" value="'.$ecc.'"><label for="action_service_'.$ecc.'">'.$ecc.'</label></div>';
                        }
                    }
                    ?>
                    <div style="clear: both;"></div>
                </div>
                使用的Service文件: (全局service)
                <div>
                    <?php
                    foreach($this->data['service'] as $ecc)
                    {
                        echo '<div class="flssord"><input type="checkbox" id="action_service_'.$ecc.'" name="action_service" value="'.$ecc.'"><label for="action_service_'.$ecc.'">'.$ecc.'</label></div>';
                    }
                    ?>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div id='action_return'>
                数据返回模式:
                &nbsp;&nbsp;&nbsp;&nbsp;Html<input type="checkbox" name="action_return" value="view">
                &nbsp;&nbsp;&nbsp;&nbsp;Flash<input type="checkbox" name="action_return" value="flashcall">
                &nbsp;&nbsp;&nbsp;&nbsp;Json<input type="checkbox" name="action_return" value="json">
                &nbsp;&nbsp;&nbsp;&nbsp;Jsonp<input type="checkbox" name="action_return" value="Jsonp">
                &nbsp;&nbsp;&nbsp;&nbsp;J7ssp<input type="checkbox" name="action_return" value="J7ssp">
<!--                &nbsp;&nbsp;&nbsp;&nbsp;Hessian<input type="checkbox" name="action_return" value="hessian">-->
            </div>
            <div>
                <input type="submit" value="Submit" onclick="submitinfo('crt_action','action_app,action_folder,action_name,action_service,action_return');">
            </div>
            <div class="content-notice showpanel" id='crt_action' style="display: none;"></div>
        </div>
    </div>



    <div class="content-notice" style="margin-top: 20px;">
        <div>
            Action Module: (会自动创建 Folder 及根class，根据模板 core/tmp/folder_common.php 创建,根继承于 **app**/common/ 下文件)
        </div>
        <div class="content-notice2 showpanel">
            <div>
                App:
                <select id='folder_rootapp' onchange="location.href='?select[folder]='+this.options[this.selectedIndex].value;">
                    <?php
                    foreach($this->data['allapps'] as $ecc)
                    {
                        if( isset($this->select['folder']) && $this->select['folder']==$ecc )
                            echo '<option value="'.$ecc.'" selected>'.$ecc.'</option>';
                        else
                            echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select> ,

                Root_Class:
                <select id='folder_rootclass'>
                    <?php
                    foreach($this->data['commonclass'] as $ecc)
                    {
                        echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select> ,
                Module_Name:<input id='folder_name'> ,
            </div>
            <div>
                <input type="submit" value="Submit" onclick="submitinfo('crt_folder','folder_name,folder_rootclass,folder_rootapp');">
            </div>
            <div class="content-notice showpanel" id='crt_folder' style="display: none;"></div>
        </div>
    </div>


    <div class="content-notice" style="margin-top: 20px;margin-bottom: 20px;">
        <div>
            Service:   (core/tmp/service.php) <a href="#" onclick="window.location.reload();" style="color: blue;">刷新本页</a>
        </div>
        <div class="content-notice2 showpanel">
            <div>
                所属App:
                <select id='service_rootapp' onchange="location.href='?select[serviceapp]='+this.options[this.selectedIndex].value;">
                    <option value="0">全局</option>
                    <?php
                    foreach($this->data['allapps'] as $ecc)
                    {
                        if( isset($this->select['serviceapp']) && $this->select['serviceapp']==$ecc )
                            echo '<option value="'.$ecc.'" selected>'.$ecc.'</option>';
                        else
                            echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select>
                ,
                Service_Name:<input id="service_name" style="text-align: right;">Service
            </div>
            <div id="service_dao">
                使用的DAO文件: (app私有)
                <div>
                    <?php
                    if( isset($this->data['servicedao']) )
                    {
                        foreach($this->data['servicedao'] as $ecc)
                        {
                            echo '<div class="flssord"><input type="checkbox" id="service_dao_'.$ecc.'" name="service_dao" value="'.$ecc.'"><label for="service_dao_'.$ecc.'">'.$ecc.'</label></div>';
                        }
                    }
                    ?>
                    <div style="clear: both;"></div>
                </div>
                使用的DAO文件: (全局dao)
                <div>
                    <?php
                    foreach($this->data['dao'] as $ecc)
                    {
                        echo '<div class="flssord"><input type="checkbox" id="service_dao_'.$ecc.'" name="service_dao" value="'.$ecc.'"><label for="service_dao_'.$ecc.'">'.$ecc.'</label></div>';
                    }
                    ?>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div>
                <input type="submit" value="Submit" onclick="submitinfo('crt_service','service_name,service_rootapp,service_dao');">
            </div>
            <div class="content-notice showpanel" id='crt_service' style="display: none;"></div>
        </div>
    </div>

    <div class="content-notice">
        <div>
            DAO:    (core/tmp/dao.php)
        </div>
        <div class="content-notice2 showpanel">
            <div>
                所属App:
                <select id='dao_rootapp'>
                    <option value="0">全局</option>
                    <?php
                    foreach($this->data['allapps'] as $ecc)
                    {
                        if( isset($this->select['folder']) && $this->select['folder']==$ecc )
                            echo '<option value="'.$ecc.'" selected>'.$ecc.'</option>';
                        else
                            echo '<option value="'.$ecc.'">'.$ecc.'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                DAO_Name:&nbsp;&nbsp;<?php echo $this->data['db_table_perfix'];?><input id="dao_name" style="text-align: right;border: 0;">DbDAO/RedisDAO
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                即表名，不含前缀后缀等
            </div>
            <div>
                table_Name:&nbsp;&nbsp;<?php echo $this->data['db_table_perfix'];?><input id="dao_tablename" style="text-align: right;border: 0;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                真实表名，不含前缀后缀等,填写系统将会去默认0数据库寻找表结构
            </div>
            <div>
                DAO基类：
                <select id="dao_rootclass">
                    <option value="CacheListDbCrudDAO">CacheListDbCrudDAO (自动MemCache缓存，有PDOMysql)</option>
                    <option value="CachePKDbCrudDAO">CachePKDbCrudDAO (自动MemCache缓存:主键，有PDOMysql)</option>

                    <option value="DbCrudDAO">DbCrudDAO (无自动缓存，有PDOMysql)</option>
<!--                    <option value="CacheRedisDbCrudDAO">CacheRedisDbCrudDAO (自动Redis缓存，有PDOMysql)</option>-->
<!--                    <option value="RedisCrudDAO">RedisCrudDAO ( 通过Redis模拟的关系型数据，无PDOMysql )</option>-->
<!--                    <option value="RedisSimpleDAO">RedisSimpleDAO ( 类似CacheRedisDbCrudDAO存储数据，无PDOMysql )</option>-->
                </select>
            </div>
            <div id="dao_pkname_d">
                _pk主键:<input id="dao_pkname">,多字段用,分隔
            </div>
            <div>
                <input type="submit" value="Submit" onclick="submitinfo('crt_dao','dao_name,dao_rootapp,dao_tablename,dao_rootclass,dao_pkname');">
            </div>
            <div class="content-notice showpanel" id='crt_dao' style="display: none;"></div>
        </div>
    </div>

