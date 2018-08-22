<?php
class ValidateMore extends J7Validate
{
    public function _validateType($data,$rule)
    {
        if( isset($rule['type']) )
        {
            switch ($rule['type']) {
                case 'validate_code':
                    $c = $data[$rule['key']] == $_SESSION['validate_code'];
                    if( $c )
                        $_SESSION['validate_code'] = null;
                    return $c;
                    break;
            }
            return parent::_validateType($data,$rule);
        }
        return true;
    }


    public static function validatecode($name='login[vcode]',$id='vcode',$tabindex=999)
    {
        $class = 'c'.$id.'_'.crc32($name.rand(1,9999));
        ?>
        <input type="text" placeholder="验证码" class="input span1 <?php echo $class;?>" id="<?php echo $id;?>" name="<?php echo $name;?>" maxlength="4" <?php echo $tabindex?'tabindex="'.$tabindex.'"':'';?> onfocus="$('#validatecode').attr('src','/__all__/index/validatecode/?'+(new Date()).getTime());$('#validatecode').show();" />
        <span class="image"><img id="validatecode" src="/res/img/common/load.gif" style="vertical-align:middle;cursor:pointer;display: none;" title="点击换图" onclick="javascript:this.src=('/__all__/index/validatecode/?'+(new Date()).getTime());$('.<?php echo $class;?>').val('');" /></span>
        <?php
    }
}