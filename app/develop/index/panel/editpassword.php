

        <div id="tablediv">
            <form action="<?php echo $this->rewrite_base;?>/index/index/tp/editpassword" method="post" onsubmit="return check()">
                <table >
                    <tr>
                        <td>新密码:</td><td><input type="password" name="password" id="new_pwd"></td>
                    </tr>
                    <tr>
                        <td>确认新密码:</td><td><input type="password" name="repassword" id="confirm_pwd"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" value="修改" name="submit"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php if(isset($this->err)){ ?><?php echo $this->err; }?></td>
                    </tr>
                </table>
            </form>
        </div>


<script>
    function check(){
        var p1 = $('#new_pwd').val();
        var p2 = $('#confirm_pwd').val();

        if(p1 == '' || p2 == ''){
            alert('填写完整信息！');
            return false;
        }
        if(p1 != p2){
            alert('两次密码输入不一致！');
            return false;
        }
        return true;
    }
</script>

