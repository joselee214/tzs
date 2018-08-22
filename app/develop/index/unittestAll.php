

<div class='content-head'>
    <div>
        TestTools 批量方法测试
    </div>
    <div style="font-size: 12px;margin: 5px 20px 10px;">
        针对 /app/develop/UnitTest/ 目录下所有Action的测试 ,  go back to <a href="/index/WebTest/">WebTest</a>
    </div>
</div>


<style type="text/css">
    .divleft {
        float: left;
    }

    .divright {
        float: right;
    }

    .divleft div {
        margin: 15px;
    }

    a.wblue {
        color: blue;
    }
    .wred{color: red;}
</style>
<div class="content-notice">
    <input type="button" value="Select Reverse All" onclick="selectall();">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" value="ExecuteTest" onclick="executeall();">
    <input type="button" value="ClearLog" onclick="clearlog();">
</div>
<script type="text/javascript">
    function clearlog()
    {
        $('#restresult div').html('&nbsp;');
        $('#logresult').val('');
    }
    function selectall() {
        $('#exectest input:checkbox[name="exectest"]').each(function (i, v) {
            if ($(this).attr("checked") == undefined) {
                $(this).attr("checked", true);
            }
            else {
                $(this).attr("checked", false);
            }
        });
    }
    var cmdpath = "<?php echo $this->rewrite_base;?>" + "/index/UnitTest/";
    function executeall() {
        $('#exectest input:checkbox[name="exectest"]:checked').each(function (i, v) {
            tourl = cmdpath + $(this).val() + '/viewtype/jsonp';
            $.getJSON( tourl+"&callback=?" , executereq );
        });
    }
    function executereq(req)
    {
        var exeresult = '';
        var infname = '';
        $.each( req , function(index, value){
            infname = value.infname;
            exeresult += value.className;
            exeresult += '->'
            exeresult += value.method;
            exeresult += ' = '
            if( value.Assert )
            {
                exeresult += value.Assert;
            }
            else
            {
                exeresult += '<span class="wred">'+value.Assert+'</span>';
            }
            exeresult += ' ; '

            if( value.msg != undefined )
            {
                $.each( value.msg , function(index, value) {
                    str = $('#logresult').val() + value + '\n';
                    $('#logresult').val( str );
                });
            }
            if( value.msgtest != undefined )
            {
                $.each( value.msgtest , function(index, value) {
                    str = $('#logresult').val() + value + '\n';
                    $('#logresult').val( str );
                });
            }

        });
        exeresult = $('#restresult_'+infname).html() + exeresult;
        $('#restresult_'+infname).html(exeresult);
    }
</script>
<div class="content-notice">
    <div class="divleft content-notice2" id="exectest">
        TestFile:
        <br/>
        <?php
        foreach ($this->data['testf'] as $eachf) {
            ?>
            <div><a href="?/UnitTest/<?php echo $eachf;?>" class="wblue" target="_blank">Exec</a>
                <input type="checkbox" id='<?php echo $eachf;?>' value="<?php echo $eachf;?>" name="exectest">
                <label for='<?php echo $eachf;?>'><?php echo $eachf;?></label>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="divleft content-notice2" style="max-width: 1000px;min-width: 300px;">
        Result:
        <br/>
        <div id="restresult">
            <?php
            foreach ($this->data['testf'] as $eachf) {
                ?>
                <div id='restresult_<?php echo $eachf;?>' onclick="window.open('?/UnitTest/<?php echo $eachf;?>','_blank');" style="cursor: pointer;">
                    &nbsp;
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="divleft">
        LogData:
        <br/>
        <textarea rows="20" cols="60" id="logresult" style="font-size: 12px;"></textarea>
    </div>
    <div style="clear: both;"></div>
</div>