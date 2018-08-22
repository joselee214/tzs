<script type="text/javascript" src="swf/swfobj.js"></script>
<script src="js/highcharts/highcharts.js"></script>
<script src="js/highcharts/exporting.js"></script>
<script src="js/highcharts/hctheme.js"></script>

<div class='content-head'>
    LogMonitor:
</div>

<div class='content-notice'>
    <?php foreach ($this->conns as $ek => $conn) { ?>
    <div style="float: left;margin:5px 10px;">
        <label for="selectlog<?php echo $ek;?>"><?php echo $conn['ip'];?>:<?php echo $conn['port'];?></label>
        <input type="checkbox"
               onclick="addlogserver(<?php echo $ek;?>,'<?php echo $conn['ip'];?>',<?php echo $conn['port'];?>)"
               id="selectlog<?php echo $ek;?>" name="selectlog[]">
    </div>
    <?php } ?>
    <div style="clear: both;"></div>
</div>

<div class='content-notice'>
    <?php foreach ($this->logpoint as $lkey=>$logpoint) { ?>
    <div style="float: left;margin:5px 10px;">
        <?php echo $logpoint[0]; ?>&nbsp;<?php echo $logpoint[1]; ?>
        <input type="checkbox"
               onclick="startlog('<?php echo $logpoint[0]; ?>','<?php echo $logpoint[1]; ?>','<?php echo str_replace('"','\\\'',$logpoint[2]); ?>');"
               id='startlog<?php echo $logpoint[0]; ?>'>
        <br/>
        <input type="text" value="<?php echo isset($this->logpointset[$lkey])?$this->logpointset[$lkey][0]:'1';?>" size="1" id='perTime<?php echo $logpoint[0]; ?>'
               style="border: 0;background: #E8EEF7;color: #1A76B7;text-align: right;">s
        <input type="button" onclick="restartlog('<?php echo $logpoint[0]; ?>');"
               value="set" style="border: 0;background: #1A76B7;color: #fff;">
        /
        <input type="text" value="<?php echo isset($this->logpointset[$lkey])?$this->logpointset[$lkey][1]:'1';?>" size="1" id='perTimeLine<?php echo $logpoint[0]; ?>'
               style="border: 0;background: #E8EEF7;color: #1A76B7;text-align: right;">s
    </div>
    <?php } ?>
    <div style="clear: both;"></div>
</div>

<div class='content-notice2' id='showcontainer'>
</div>

<div class='content-notice2' id="logserverarea">
    <div style="float: left;margin:5px 10px;">
        <input type="button" id="btnClear" value="ClearTxt" onclick="btnClear_Click()">
        <label for="ViewLog">总监控log</label> <input type="checkbox" checked="true" id='ViewLog' onclick="changestatus('ViewLog');">
        <br/>
        <textarea id="testlog" rows="5" cols="20"></textarea>
    </div>
    <?php foreach ($this->conns as $ek => $conn) { ?>
    <div style="float: left;margin:5px 10px;">
        <div id='zlibsocket<?php echo $ek;?>'></div>
        <div>
            <input type="button" id="btnClear" value="ClearTxt" onclick="btnClear_Click(<?php echo $ek;?>)">
            <label for="ViewLog<?php echo $ek;?>">log</label> <input type="checkbox" checked
                                                                         id='ViewLog<?php echo $ek;?>'
                                                                         onclick="changestatus('ViewLog<?php echo $ek;?>');">
            <br/>
            <textarea id="testlog<?php echo $ek;?>" rows="5" cols="30"></textarea></div>
    </div>
    <?php } ?>
    <div style="clear: both;"></div>
</div>

<script type="text/javascript">


    var swfobject_flashurl = '<?php echo $this->adminrootfiles;?>swf/ZLibSocket.swf';
    var swfobject_params = {
        'allowScriptAccess' : 'always',
        'allowFullScreen' : 'true',
        'wmode' : 'opaque',
        'flashvars' : '',
        'movie' : swfobject_flashurl
    };
    <?php foreach ($this->conns as $ek => $conn) { ?>
    swfobject.embedSWF(swfobject_flashurl, "zlibsocket<?php echo $ek;?>", "1", "1", "10.0.0", "<?php echo $this->adminrootfiles;?>js/expressInstall.swf", {}, swfobject_params, {});
        <?php } ?>

    var nowconnectid = 0;
    var allstatus = new [];

    function changestatus(objid) {
        if (allstatus[objid] == undefined || allstatus[objid] == 0) {
            allstatus[objid] = 1;
        }
        else {
            allstatus[objid] = 0;
        }
    }

    function addlogserver(sid, ip, port) {
        nowconnectid = sid;
        toSocketObj = $('#zlibsocket' + sid)[0];
        if (allstatus['checked'+sid] == undefined || allstatus['checked'+sid] == 0 || allstatus['checked'+sid] == 1) {
            //新增监视服务器
            port = parseInt(port);
            toSocketObj.connect(ip, port);
            //绑住所有checkbox对象
            $("#selectlog" + sid).attr("checked", false);
            allstatus['checked'+sid] = 1;
            $('input[name="selectlog[]"]').each(function() {
                $(this).hide();
            });
        }
        else {
            //去除监视服务器
            allstatus['checked'+sid] = 0;
            toSocketObj.disconnect();
        }
        setTimeout(showselectlog, 2000);
    }
    function showselectlog() {
        $('input[name="selectlog[]"]').each(function() {
            $(this).show();
        });
    }

    var logdata = new [];
    //接受数据
    function ZLibSocket_Recv(str) {
        var sid = false;
        var recvdata;
        var needoutlog = true;
        var needoutlogjson = true;
        try {
            if (str.substr(0, 1) == '{' && str.substr(-3, 1) == '}') {
                recvdata = JSON.parse(str);
                sid = recvdata.j7serverindex;
                switch (recvdata.j7act) {
                    case 'admin':
                        if (recvdata.j7succ == 1) {
                            allstatus['checked'+recvdata.j7serverindex] = 2;
                            $("#selectlog" + recvdata.j7serverindex).attr("checked", true);
                            showselectlog();
                        }
                        break;
                    case 'logmonitor':
                        data = recvdata.data;
                        $.each(data, function(logmonitor, monitorValue) {
                            if (allstatus['startlog' + logmonitor] != 0 && allstatus['startlog' + logmonitor] != undefined) {
                                if (logdata[logmonitor] == undefined) {
                                    logdata[logmonitor] = [];
                                    if( typeof(monitorValue) == 'object' )
                                    {
                                        logdata[logmonitor] = monitorValue;
                                    }
                                    else
                                    {
                                        logdata[logmonitor][0] = parseFloat(monitorValue);
                                    }
                                    logdata['j7t_' + logmonitor] = 0;
                                }
                                else
                                {
                                    if( typeof(monitorValue) == 'object' )
                                    {
                                        $.each( monitorValue , function(iii, vvvv)
                                        {
                                            logdata[logmonitor][iii] += vvvv;
                                        });
                                    }
                                    else if ( typeof(monitorValue) == 'number' )
                                    {
                                        logdata[logmonitor][0] += parseFloat(monitorValue);
                                    }
                                    logdata['j7t_' + logmonitor] += 1;
                                }
                            }
                        });
                        needoutlog = false;
                        needoutlogjson = false;
                        break;
                    case 'debug':
                        if (allstatus['ViewLog' + sid] == undefined || allstatus['ViewLog' + sid] == 0) {
                                delete recvdata.j7succ;
                                delete recvdata.j7serverindex;
                                delete recvdata.j7act;
                                console.log( '===  '+recvdata.j7debug+'  ===' );
                                console.log( recvdata.j7debuginfo );
                        }
                            needoutlog = false;
                            needoutlogjson = false;
                        break;
                    default:
                        delete recvdata.j7succ;
                        delete recvdata.j7serverindex;
                        break;
                }
            }
            else {
                showselectlog();
            }
        }
        catch(e) {
            log(str);
        }
        //即时日志记录显示
        if(needoutlogjson)
        {
            log(recvdata);
        }
        if( needoutlog )
        {
            if (sid === false) {
                if (allstatus['ViewLog'] == undefined || allstatus['ViewLog'] == 0) {
                    str += $('#testlog').val();
                    $('#testlog').val(str);
                }
            }
            else {
                if (allstatus['ViewLog' + sid] == undefined || allstatus['ViewLog' + sid] == 0) {
                    str += $('#testlog' + sid).val();
                    $('#testlog' + sid).val(str);
                }
            }
        }
    }

    function ZLibSocket_Connect() {
        toSocketObj = $('#zlibsocket' + nowconnectid)[0];
        var d = new Date();
        var hh = d.getHours();
        var mm = d.getMinutes();
        var ss = d.getSeconds();
        auid = hh + mm + ss + '' + <?php echo $this->myuid;?> + nowconnectid;
        auid = -auid;
        ccomdsend = JSON.parse('<?php echo json_encode($this->logadmin);?>');
        ccomdsend.uid = auid;
        toSocketObj.send(JSON.stringify(ccomdsend));
    }
    function btnClear_Click(sid) {
        if (sid == undefined)
            $('#testlog').val("");
        else
            $('#testlog' + sid).val("");
    }
    function ZLibSocket_Disconnect() {
        ZLibSocket_Recv('Server Disconnect \n');
    }
    function ZLibSocket_Error(s) {
        ZLibSocket_Recv('Error : ' + s + '\n');
    }
    function parseObj(strData) {
        return (new Function("return " + strData))();
    }

    function getTimeString() {
        var d = new Date();
        var hh = d.getHours();
        var mm = d.getMinutes();
        var ss = d.getSeconds();
        return get00(hh) + ":" + get00(mm) + ":" + get00(ss);
    }
    function get00(n) {
        return (n < 10 ? "0" : "") + n;
    }

</script>


<script type="text/javascript">
    function getData(series, type, ltp) {
        var x = (new Date()).getTime();
        var yl = [];
        if (logdata[type] == undefined) {
            logdata[type] = [];
            $.each( ltp[0] , function(index, value){ logdata[type][index]=0;yl[index]=0;});
            logdata['j7t_' + type] = 0;
        }
        var y = logdata[type];
        var yt = logdata['j7t_' + type];

        try
        {
            eval( ltp[1] );
            $.each( ltp[0] , function(index, value){
                if( isNaN(yl[index]) )
                {
                    yl[index] = 0;
                }
                series[index].addPoint([x, yl[index]], true, true);
            });
        }
        catch(e)
        {
            $.each( ltp[0] , function(index, value){
                series[index].addPoint([x, 0], true, true);
            });
        }

        //恢复统计
        logdata[type] = [];
        $.each( ltp[0] , function(index, value){ logdata[type][index]=0;});
        logdata['j7t_' + type] = 0;
    }

    function startshowpanel(logid, logname , ltp) {
        var chart;
        var initseries = [];
        var leftname='';

        $.each( ltp[0] , function(index, value) {
            leftname += ' '+value+' , ';
            var einitseries =  {
                    name: value,
                    data: (function() {
                        var data = [],
                                time = (new Date()).getTime(),
                                i;
                        for (i = -150; i <= 0; i++) {
                            data.push({
                                x: time + i * parseInt($('#perTimeLine' + logid).val()) * 1000,
                                y: 0
                            });
                        }
                        return data;
                    })()
                };
            initseries.push(einitseries);
        });

        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container_' + logid,
                type: 'spline',
                marginRight: 10,
                events: {
                    load: function() {
                        var series = this.series;
                        getInterval(series, logid);
                    }
                }
            },
            title: {
                text: logid + ' ' + logname
            },
            xAxis: {
                type: 'datetime',
                tickPixelInterval: 100
            },
            yAxis: {
                title: {
                    text: leftname
                },
                min: 0,
                plotLines: [
                    {
                        value: 1,
                        width: 1,
                        color: '#808080'
                    }
                ]
            },
            tooltip: {
                formatter: function() {
                    return '<b>' + this.series.name + '</b><br/>' +
                            Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) + '<br/>监控值:<b>' +
                            this.y + '</b>'
                }
            },
            plotOptions: {
                spline: {
                    lineWidth: 2,
                    states: {
                        hover: {
                            lineWidth: 5
                        }
                    },
                    marker: {
                        enabled: false,
                        states: {
                            hover: {
                                enabled: true,
                                symbol: 'circle',
                                radius: 5,
                                lineWidth: 1
                            }
                        }
                    },
                    pointInterval: 3600000, // one hour
                    pointStart: Date.UTC(2009, 9, 6, 0, 0, 0)
                }
            },
            legend: {
                enabled: false
            },
            exporting: {
                enabled: false
            },
            series: initseries
        });
        allstatus['sp_Interval_chart' + logid] = chart;
    }

    function getInterval(series, logid) {
        allstatus['sp_Interval_series' + logid] = series;
        var ltp = allstatus['sp_Interval_series_info'+logid];
        var intvaltime = parseInt($('#perTime' + logid).val());
        allstatus['sp_Interval' + logid] = setInterval(function() {
            getData(series, logid, ltp);
        }, intvaltime * 1000);
    }

    function startlog(logid, logname , ltp) {
        objid = 'startlog' + logid;
        changestatus(objid);
        ltp = eval(ltp);
        allstatus['sp_Interval_series_info' + logid] = ltp;

        if (allstatus[objid] == 0) {
            //关闭
            $("#startlog" + logid).attr("checked", false);
            if (allstatus['sp_Interval_series' + logid] != undefined) {
                delete allstatus['sp_Interval_series' + logid];
            }
            if (allstatus['sp_Interval' + logid] != undefined) {
                clearInterval(allstatus['sp_Interval' + logid]);
                delete allstatus['sp_Interval' + logid];
            }
            if (allstatus['sp_Interval_chart' + logid] != undefined) {
                allstatus['sp_Interval_chart' + logid].destroy();
                delete allstatus['sp_Interval_chart' + logid];
            }
            $("#container_" + logid).remove();
        }
        else {
            //开启
            $("#startlog" + logid).attr("checked", true);
            $("#showcontainer").append('<div id="container_' + logid + '" class="containerin"></div>');
            startshowpanel(logid, logname , ltp);
        }
    }
    function restartlog(logid) {
        if (allstatus['sp_Interval' + logid] != undefined && allstatus['sp_Interval_series' + logid] != undefined) {
            clearInterval(allstatus['sp_Interval' + logid]);
            var series = allstatus['sp_Interval_series' + logid];
            getInterval(series, logid);
        }
    }
</script>
<style type="text/css">
    .containerin {
        min-width: 400px;
        height: 300px;
        margin: 5px auto;
    }
</style>

