
<div class='content-head'>
    <div>
        TestTools 方法调试
    </div>
    <div style="font-size: 12px;margin: 5px 20px 10px;">
        仅在DEV上实现，主要是辅助phper进行方法调试，注意使用；注意各个参数大小写 , <a href="<?php echo $this->rewrite_base;?>/index/WebTest/~executeall">批量测试</a>
    </div>
</div>

<style type="text/css">
    .showpanel {
    }

    .showpanel div {
        margin:10px 0;
    }

    .tai {
        font-size: 15px;
        padding: 3px;
    }

    .bakro {
        background: none repeat scroll 0 0 #FFFEE7;
        font-size: 12px;
        padding: 1px;
    }

    select {
        line-height: 25px;
    }
</style>

<div class="content-notice">
    <div class="content-notice2">
        <div style="float: left;max-width: 360px;" class="content-notice showpanel">
            <div class="content-notice2">
                Type:
                <select name="stype" id="stype" onchange="changeselect('stype');">
                    <option value="">请选择</option>
                    <option value="Action">Action</option>
                    <option value="service">Service</option>
                    <option value="dao">DAO</option>
                </select>
                Application:
                <select name="app" id="app" onchange="changeselect('app');">
                    <option value="">请选择</option>
                </select>
                Module&Class:
                <select name="folder" id="folder" onchange="changeselect('folder');">
                    <option value="">请选择</option>
                </select>
                <select name="class" id="class" onchange="changeselect('class');">
                    <option value="">请选择</option>
                </select>
                Method:
                <select name="method" id="method" onchange="changeselect('method');">
                    <option value="">请选择</option>
                </select>
            </div>
            <div>
                InitParams，类初始化参数:<br/>
                <textarea rows="1" cols="50" class="tai bakro" readonly id="InitParams_sample"></textarea>
                <br/>
                <input type="text" size="39" class="tai" id="initparams"/>
                <br/>
                请注意参数书写格式,json格式,以 {"p1":"ss","p2":"ss22"} 传入,注意键值名对应
            </div>
            <div>
                CallParams，方法执行参数:<br/>
                <textarea rows="4" cols="50" class="tai bakro" readonly id="CallParams_sample"></textarea>
                <br/>
                <input type="text" size="39" class="tai" id="callparams"/>
                <br/>
                请注意参数书写格式,json格式,自动过滤换行符的会,为空即为null，以 [123,"ssd",{"p1":"ss","p2":"ss22"}] 方式传入，按行分个数，按顺序传入对应个数值
            </div>

            <div>
                OutPut:<br/>
                <div id='testlogstr'></div>
                <div>
                    <textarea rows="10" cols="40" id='testlog'></textarea>
                </div>
            </div>
        </div>
        <div style="float: left;">
            <input type="button" id="submittest" value="提交测试" onclick="autoexecute(0);changeselect('submit');">
            <input type="button" id="clear" value="清除测试过程信息"
                   onclick="$('#testlog').val('');$('#testlogplus').val('');$('#testlogstr').html('');">
            <input type="button" id="savetest" value="保存测试" onclick="changeselect('savesubmit');">

            <label for="autoexecute">检测文件修改自动执行</label>
            <input type="checkbox" name="autoexecute" id="autoexecute" value="autoexecute" onclick="autoexecute();">
            <span id="loadingspan"></span>

            <br/>
            Trace:<br/>
            <div>
                <div id='logparam'>
                    跟踪 <label for="logparam_inparam">参数</label> <input type="checkbox" id='logparam_inparam'
                                                                       value="inparam" name="logparam" checked>
                    <label for="logparam_DataLog">DataLog</label> <input type="checkbox" id='logparam_DataLog'
                                                                         value="DataLog" name="logparam">
                    <label for="logparam_DoingSql">DoingSql</label> <input type="checkbox" id='logparam_DoingSql'
                                                                           value="DoingSql" name="logparam">
                    <label for="logparam_ret">结果</label> <input type="checkbox" id='logparam_ret' value="ret"
                                                                name="logparam" checked>
                </div>
                <div>
                    <textarea rows="38" cols="100" class="tai bakro" id='testlogplus'></textarea>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
</div>

<script type="text/javascript">
    var cmdpath = "<?php echo $this->rewrite_base;?>" + "/index/WebTest/";
    //$('#app').hide();
    $('#folder').hide();
    $('#submittest').hide();
    $('#savetest').hide();
    function initselect(sid, data) {
        if (data == undefined) {
            $('#' + sid).empty();
        }
        else {
            $.each(data, function (index, value) {
                $('#' + sid).append("<option value='" + value + "'>" + value + "</option>");
            });
        }
    }
    var autoexecuteintval;
    var autoexecutechecked=0;
    var autoexecutelock=0;
    function autoexecute(mods)
    {
        if (mods == 0) {
            $('#autoexecute').attr("checked", false);
         }
        if( $('#autoexecute').attr("checked")=='checked' )
        {
            autoexecutechecked=1;
            autoexecuteintval=window.setInterval('autoexecuteexec()', 1000);
        }
        else
        {
            autoexecutechecked=0;
            if( autoexecuteintval )
            {
                window.clearInterval(autoexecuteintval);
            }
        }
    }
    function autoexecuteexec()
    {
        if( autoexecutelock==0 )
        {
            changeselect("submit");
            autoexecutelock = 1;
        }
    }
    function changeselect(clitype) {
        if (clitype == 'stype') {
            $('#folder').hide();
            //$('#app').hide();

            initselect('app');
            initselect('folder');
            initselect('class');
            initselect('method');

            if ($('#stype').val() == 'Action') {
                $('#folder').show();
                $('#app').show();
            }
        }
        else if (clitype == 'app') {
            initselect('folder');
            initselect('class');
            initselect('method');
        }
        else if (clitype == 'folder') {
            initselect('class');
            initselect('method');
        }
        else if (clitype == 'class') {
            initselect('method');
        }
        var tourl = cmdpath + '~changeselect/?clitype=' + clitype + '&param[stype]=' + $('#stype').val() + '&param[app]=' + $('#app').val() + '&param[folder]=' + $('#folder').val() + '&param[class]=' + $('#class').val() + '&param[method]=' + $('#method').val();
        if (clitype == 'submit' || clitype == 'savesubmit') {
            tourl = tourl + '&autoexecutechecked='+autoexecutechecked;
            tourl = tourl + '&param[initparams]=' + $('#initparams').val() + '&param[callparams]=' + $('#callparams').val();
            tourl = tourl + '&param[logparam]='
            $('#logparam input:checkbox[name="logparam"]:checked').each(function (i, v) {
                tourl = tourl + $(this).val() + ',';
            });
        }
        //log( tourl );
        $('#loadingspan').html('...');
        log(tourl);
        $.getJSON(tourl + "&callback=?", function (req) {
            $('#loadingspan').html('');
            if (clitype == 'stype') {
                if ($('#stype').val() == 'Action') {
                    initselect('app', req.app);
                    initselect('folder', req.folder);
                    initselect('class', req.class);
                    initselect('method', req.method);
                }
                else {
                    initselect('app', req.app);
                    initselect('class', req.class);
                    initselect('method', req.method);
                }
            }
            else if(clitype == 'app'){
                initselect('folder', req.folder);
                initselect('class', req.class);
                initselect('method', req.method);
            }
            else if (clitype == 'folder') {
                initselect('class', req.class);
                initselect('method', req.method);
            }
            else if (clitype == 'class') {
                initselect('method', req.method);
            }
            $('#submittest').show();
            $('#savetest').show();
            if (clitype == 'submit') {
                autoexecutelock = 0;
                if( req.ret !='J7AUTOEXECUTE' )
                {
                    log('Test Call Return :');
                    log(req.ret);
                    if (req.ret != undefined && typeof(req.ret) == 'object') {
                        msg(req.ret, 'testlog', 'showobj', 1);
                    }
                    else {
                        msg(req.ret, 'testlog', 'showstr', 1);
                    }
                    msg(req.msg, 'testlog', 'listmsg', 1);
                    msg(req.msgtest, 'testlogplus', 'listmsg', 1);
                }
            }
            else if (clitype == 'savesubmit') {
                $('#testlogstr').html('');
                $.each(req.strmsg, function (index, value) {
                    str = $('#testlogstr').html();
                    str += value + '<br/>';
                    $('#testlogstr').html(str);
                });
            }
            else {
                if (req.param != undefined) {
                    $.each(req.param, function (index, value) {
                        if (value != 'null') {
                            $('#' + index).val(value);
                        }
                    });
                }
                msg(req.msg, 'testlog', 'listmsg', 1);
                msg(req.props, 'InitParams_sample', 'showparams');
                if (req.DocComment != undefined) {
                    $('#CallParams_sample').val(req.DocComment);
                }
                msg(req.parameters, 'callparams', 'showobj');
            }
        });
    }
    function msg(data, toid, showtype, clearold) {
        if (toid == undefined) {
            toid = 'testlog';
        }
        if (showtype == undefined) {
            showtype = 'listmsg';
        }
        if (clearold != 1) {
            $('#' + toid).val('');
        }
        if (data != undefined) {

            if( $('#' + toid).val() !='' )
            {
                str = $('#' + toid).val() + '------------------------------------------------------\n';
                $('#' + toid).val(str);
            }

            if (showtype == 'listmsg') {
                $.each(data, function (index, value) {
                    str = $('#' + toid).val() + value + '\n';
                    $('#' + toid).val(str);
                });
            }
            else if (showtype == 'showparams') {
                $.each(data, function (index, value) {
                    str = $('#' + toid).val() + index + '=' + value + '\n';
                    $('#' + toid).val(str);
                });
            }
            else if (showtype == 'showstr') {
                str = $('#' + toid).val() + data + '\n';
                $('#' + toid).val(str);
            }
            else if (showtype == 'showobj') {
                str = $('#' + toid).val() + JSON.stringify(data) + '\n';
                $('#' + toid).val(str);
            }
            $('#' + toid).scrollTop(9999);
        }
    }
</script>
