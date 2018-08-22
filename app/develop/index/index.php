<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title>Login</title>
</head>
<style type="text/css">
    table {
        border: none;
    }

    table td {
        line-height: 200%;
    }
    .content-head
    {
        font-size: 20px;
        color:#1a76b7;
        margin: 5px 10px;
    }

    .content-notice
    {
        background:#E8EEF7;
        color:#1a76b7;
        margin: 5px 10px;
        padding: 5px;
    }
</style>
<script type="text/javascript">
    //<![CDATA[
    if (window!=parent) { // 防止 iframe 里 出现登录窗口
        window.top.location.href = '<?php echo $this->rewrite_base;?>/';
    }
    //]]>
</script>
<body class="content-notice">
<div style="width:100%;height: 100% ">
    <div style="margin: -100px 0 0 -130px;position: absolute;left: 50%;top: 50%;background: #ffffff;padding: 10px 30px;">
        <div class='content-head'>
            Login:
        </div>
        <div id="tablediv">
            <form action="<?php echo $this->rewrite_base;?>/index/index" method="post">
                <table>
                    <tr>
                        <td>UserName</td><td><input type="text" name="name"></td>
                    </tr>
                    <tr>
                        <td>PassWord</td><td><input type="password" name="password"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="Login" name="submit"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php if(isset($this->err)){ ?><?php echo $this->err; }?></td>
                    </tr>
                </table>
            </form>

        </div>
    </div>
</div>
</body>
</html>