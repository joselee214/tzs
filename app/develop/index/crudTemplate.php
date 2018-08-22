<?php
$cmdpath = $_->rewrite_base.'/index/crudTemplate/?';
?>

<style>
    input{width: 100px;}
    .red{color:red;}
</style>

<div class='content-head'>
    <div>
        html表单 辅助设计器:
    </div>
    <div style="font-size: 12px;margin: 5px 20px 10px;">
        仅在DEVELOP，主要是辅助phper，创建表单；有一定局限性，注意使用；
    </div>
</div>

<div class="content-notice">
    <div>
        Table 表名 (不使用DAO名)
    </div>
    <div class="content-notice2 showpanel">
        <form action="<?php echo $cmdpath;?>act=table" method="post">
        <div>
            Table_name:&nbsp;&nbsp;<?php echo $this->data['db_table_perfix'];?>
            <input id="table_name" style="text-align: right;border: 2;width: 200px;" value="<?php echo isset($this->param['table_name'])?$this->param['table_name']:'';?>" name="param[table_name]">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            扩展行列数<input name="extrownums" value="<?php echo $_->extrownums;?>">
        </div>
        <div>
            <input type="submit" value="Read_Table_Cols">
        </div>
        </form>

        <form action="<?php echo $cmdpath;?>act=create" method="post">
        <?php if(isset($this->param['tablestr']))
        {
            echo '<div class="content-notice">';
            ?>
            <div>
                总的id :<input type="text" name="param[table_name]" value="<?php echo isset($this->param['table_name'])?$this->param['table_name']:'';?>">
            </div>
            <?php
            echo '<div><table>';
            ?>
            <tr>
                <td>字段名</td>
                <td>属性</td>
                <td>表默认值</td>
                <td>展现在表单?</td>
                <td>显示名</td>
                <td>input设置name</td>
                <td>默认值</td>
                <td>展现形式</td>
            </tr>
            <?php
            foreach($this->param['tablestr'] as $eachrow)
            {

            ?>
            <tr>
                <td style="background: #0b77b7;">
                    <input name="cparam[<?php echo $eachrow['field'];?>][field]" value="<?php echo $eachrow['field'];?>" readonly size="5" style="background: #0b77b7;">
                </td>
                <td style="background: #0b77b7;">
                    <input name="cparam[<?php echo $eachrow['field'];?>][type]" value="<?php echo $eachrow['type'];?>" readonly size="5" style="background: #0b77b7;">
                </td>
                <td style="background: #0b77b7;">
                    <input name="cparam[<?php echo $eachrow['field'];?>][default]" value="<?php echo $eachrow['default'];?>" readonly size="5" style="background: #0b77b7;">
                </td>
                <td>
                    <input type="checkbox" name="cparam[<?php echo $eachrow['field'];?>][isneed]" <?php echo (isset($eachrow['isneed'])&&$eachrow['isneed'])?'checked':'';?>>
                </td>
                <td>
                    <input type="text" name="cparam[<?php echo $eachrow['field'];?>][showname]" value="<?php echo isset($eachrow['showname'])?$eachrow['showname']:$eachrow['field']?>">
                </td>
                <td>
                    <input type="text" name="cparam[<?php echo $eachrow['field'];?>][colname]" value="<?php echo isset($eachrow['colname'])?$eachrow['colname']:$eachrow['field'];?>">
                </td>
                <td>
                    <input type="text" name="cparam[<?php echo $eachrow['field'];?>][dvalue]" value="<?php echo isset($eachrow['dvalue'])?$eachrow['dvalue']:$eachrow['default'];?>">
                </td>
                <td> <select name="cparam[<?php echo $eachrow['field'];?>][showtp]">
                        <option value="text" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='text')?'selected':'';?>>input_text</option>
                        <option value="select" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='select')?'selected':'';?>>select</option>
                        <option value="radio" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='radio')?'selected':'';?>>radio</option>
                        <option value="checkbox" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='checkbox')?'selected':'';?>>checkbox</option>
                        <option value="hidden" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='hidden')?'selected':'';?>>hidden</option>
                        <option value="textarea" <?php echo (isset($eachrow['showtp'])&&$eachrow['showtp']=='textarea')?'selected':'';?>>textarea</option>
                    </select> </td>
            </tr>
            <?php
            }

            echo '</table></div>';

            ?>
            <div>
                展现格式 ：Table:<input type="radio" value="table" name="param[table_type]" <?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='div')?'':'checked';?>>
                Div:<input type="radio" value="div" name="param[table_type]" <?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='div')?'checked':'';?>>
            </div>
            <div>
                预留扩展列数:<input type="text" name="param[ext_cols]" value="<?php echo isset($this->param['ext_cols'])?$this->param['ext_cols']:'0';?>">
                列扩展数码，0则为最基本的两列
            </div>
            <div>
                tr每行id辅助<input type="text" name="param[trid_prefix]" value="<?php echo isset($this->param['trid_prefix'])?$this->param['trid_prefix']:'';?>"><span style="color:red;">√</span>  形成类似 <tr|div id = "prefix_***col***"></tr> 这样的id辅助 <span style="color: red;">可以空</span>
            </div>
            <div>
                td每格子class样式辅助<input type="text" name="param[css_prefix]" value="<?php echo isset($this->param['css_prefix'])?$this->param['css_prefix']:'col_';?>"><span style="color:red;">√</span>  形成类似 class = "col_name" col_value col_extcol0 col_extcol1 这样的css辅助 <span style="color: red;">可以空</span>
            </div>
            <div>
                提交表单name前后缀:
                <input type="text" name="param[name_prefix]" value="<?php echo isset($this->param['name_prefix'])?$this->param['name_prefix']:'input[';?>">
                <input type="text" name="param[name_endfix]" value="<?php echo isset($this->param['name_endfix'])?$this->param['name_endfix']:']';?>">
                形成类似 name = "input[id]" 这样的name辅助
            </div>
            <div>
                提交表单id前后缀:
                <input type="text" name="param[id_prefix]" value="<?php echo isset($this->param['id_prefix'])?$this->param['id_prefix']:'_form_';?>">
                形成类似 id = "_form_id" 这样的id辅助 , <span style="color: red;">可以空</span>
            </div>
            <div>
                提交表单php变量:
                <input type="text" name="param[php_prefix]" value="<?php echo isset($this->param['php_prefix'])?$this->param['php_prefix']:'$this->input';?>">
                <span style="color:red;">√</span>
                <input type="text" name="param[php_nextfix]" value="<?php echo isset($this->param['php_nextfix'])?$this->param['php_nextfix']:'$this->item';?>">
                以数组构成 value = "&lt;?php echo isset($input['name'])?$input['name']:(isset($item['name'])?$item['name']:__default__)?&gt;" 这样的id辅助 , <span style="color: red;">可以空</span>
            </div>
            <div>
                <hr>
            </div>
            <div>
                提交文件目标 : <input type="text" name="param[target_name]" value="<?php echo isset($this->param['target_name'])?$this->param['target_name']:'';?>">
                /**/** 形式
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Service : <input type="text" name="param[target_service]" value="<?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>">
                无需 Service 后缀&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <?php
                if( empty($this->param['target_dao']) )
                {
                    $p = explode('_',$this->param['table_name']??'');
                    $dao_name = '';
                    foreach ($p as $ep)
                    {
                        if( $ep )
                            $dao_name .= strtoupper(substr($ep,0,1)).substr($ep,1);
                    }
                }
                else
                    $dao_name = $this->param['target_dao'];
                ?>


                Service主方法 : <input type="text" name="param[target_servicemethod]" value="<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:$dao_name;?>">
                形成get***ByPK方法&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                DAO : <input type="text" name="param[target_dao]" value="<?php echo $dao_name; ?>"> 无需 DAO 后缀

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                app : <input type="text" name="param[target_app]" value="<?php echo isset($this->param['target_app'])?$this->param['target_app']:'%';?>">
            </div>
            <div>

            </div>
            <div>

            </div>
            <div>
                <hr>
            </div>
            <div>
                <input type="submit" value="SUBMIT">
            </div>
    </div>
            <?php
            if( $_->act=='create' )
            {
                ?>
                <div style="text-align: left;">
                    <?php
                    if( isset($this->param['target_name']) )
                    {
                        $folderandaction = explode('/', trim($this->param['target_name'],'/'));
                    }
                    if( !isset($folderandaction) || !isset($folderandaction[0]) ||  !isset($folderandaction[1]) )
                    {
                        $folderandaction = array('__folder__','__action__');
                    }
                    ?>
                    帮助: &nbsp;&nbsp;&nbsp;&nbsp; 约束div float的css &nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;&nbsp; .form_tr,.list_tr{overflow:auto;_height: 1%; zoom:1;}
                    <br>
                    <span class="red">* dao *</span>
                    php jsj.php c dao table=<?php echo isset($this->param['table_name'])?$this->param['table_name']:'';?>  db=0  p=<?php echo isset($this->param['target_app'])?$this->param['target_app']:'%';?>   [ 可选参数 pk=id 手动指定主键,多字段逗号分割 ; 手动指定daoName 就 n=<?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?> ]

                    <br>
                    <span class="red">* service *</span>
                    php jsj.php c service n=<?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?> p=<?php echo isset($this->param['target_app'])?$this->param['target_app']:'%';?>

                    <br>
                    <span class="red">* folder *</span>
                    php jsj.php c module n=<?php echo $folderandaction[0];?> app=<?php echo $this->param['target_app']!='%'?$this->param['target_app']:'必填';?>

                    <br>
                    <span class="red">* action *</span>
                    php jsj.php c action n=<?php echo $folderandaction[1];?> module=<?php echo $folderandaction[0];?> app=<?php echo $this->param['target_app']!='%'?$this->param['target_app']:'必填';?> ... return=view,jsonp

                    <br/>
                    命令行参数 _reset=1 意味着覆盖文件

                </div>
                <div>








<!--                    Form Result:-->


                    <div class="content-notice" style="float: left;width:750px;">
                        Form Result:
                        <div class="content-notice2">
<textarea cols="100" rows="20">
<form action="&lt;?php echo $this->link;?&gt;~post" method="POST">
<?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='table')?'<table id="'.$this->param['table_name'].'_form_table">'.chr(13):'<div id="'.$this->param['table_name'].'_form_div">'.chr(13);?>
<?php
$extcol = '';$this->param['ext_cols'] = intval($this->param['ext_cols']);
if( $this->param['ext_cols']>0 )
for($k=0;$k<$this->param['ext_cols'];$k++)
{
    $css = ($this->param['css_prefix'])?' class="'.$this->param['css_prefix'].'ext'.$k.'"':'';
    $extcol .= ($this->param['table_type']=='div')?'            <div'.$css.'>  </div>':'            <td'.$css.'>  </td>';
    $extcol .= chr(13);
}

$this->param['tablestr']['_submit'] = array(
    'field'=>'submit',
    'type'=>'button',
    'default'=>'',
    'isneed'=>'on',
    'showname'=>'',
    'colname'=>'submit',
    'dvalue'=>'',
    'showtp'=>'submit'
);

//循环生成开始
foreach($this->param['tablestr'] as $eachrow)
{

    if(isset($this->param['table_type'])&&$this->param['table_type']=='div')
    {
        if(  $_->param['trid_prefix'] )
            $tr = '<div id="'.$_->param['trid_prefix'].$eachrow['field'].'" class="form_tr">'.chr(13);
        else
            $tr = '<div class="form_tr">'.chr(13);
        $td = 'div';
        $endtr = '</div>'.chr(13);
    }
    else
    {
        if(  $_->param['trid_prefix'] )
            $tr = '<tr id="'.$_->param['trid_prefix'].$eachrow['field'].'">'.chr(13);
        else
            $tr = '<tr>'.chr(13);
        $td = 'td';
        $endtr = '</tr>'.chr(13);
    }

    if( !isset($eachrow['isneed']) || !$eachrow['isneed'] )
        continue;
    $cssname = ($this->param['css_prefix'])?' class="'.$this->param['css_prefix'].'name"':'';
    $cssvalue = ($this->param['css_prefix'])?' class="'.$this->param['css_prefix'].'value"':'';
        ?>
        <?php echo $tr;?>
            <<?php echo $td.$cssname;?>> <?php echo $eachrow['showname'];?> </<?php echo $td;?>>
            <<?php echo $td.$cssvalue;?>>
<?php
$inputid = $this->param['id_prefix']?' id="'.$this->param['id_prefix'].$eachrow['colname'].'"':'';
if( $_->param['php_prefix'] )
{
    if( $_->param['php_nextfix'] )
    {
        $dvalue = '&lt;?php echo isset('.$_->param['php_prefix'].'[\''.$eachrow['colname'].'\'])?'.$_->param['php_prefix'].'[\''.$eachrow['colname'].'\']:(isset('.$_->param['php_nextfix'].'[\''.$eachrow['colname'].'\'])?'.$_->param['php_nextfix'].'[\''.$eachrow['colname'].'\']:\''.$eachrow['dvalue'].'\');?&gt;';
    }
    else
    {
        $dvalue = '&lt;?php echo isset('.$_->param['php_prefix'].'[\''.$eachrow['colname'].'\'])?'.$_->param['php_prefix'].'[\''.$eachrow['colname'].'\']:\''.$eachrow['dvalue'].'\';?&gt;';
    }
}
else
{
    $dvalue = $eachrow['dvalue'];
}
if($eachrow['showtp']=='select')
{
    echo '              <select'.$inputid.' name="'.$this->param['name_prefix'].$eachrow['colname'].$this->param['name_endfix'].'">
                    <option value="'.$dvalue.'">'.$dvalue.'</option>
                </select>'.chr(13);
}
elseif($eachrow['showtp']=='textarea')
{
    echo '              <textarea'.$inputid.' name="'.$this->param['name_prefix'].$eachrow['colname'].$this->param['name_endfix'].'">'.$dvalue.'&lt;/textarea&gt;'.chr(13);
}
elseif($eachrow['showtp']=='submit')
{
    echo '              <input'.$inputid.' type="submit" name="'.$this->param['name_prefix'].$eachrow['colname'].$this->param['name_endfix'].'" value="submit">'.chr(13);
}
else
{
    echo '              <input'.$inputid.' type="'.$eachrow['showtp'].'" name="'.$this->param['name_prefix'].$eachrow['colname'].$this->param['name_endfix'].'" value="'.$dvalue.'">'.chr(13);
}
?>
            </<?php echo $td;?>>
<?php echo $extcol;?>
        <?php echo $endtr;?>
<?php
}
?>
<?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='table')?'</table>'.chr(13):'</div>'.chr(13);?>
</form>
</textarea>
                        </div>
                    </div>





<!--                    List Result:-->




                    <div class="content-notice" style="float: left;width:400px;margin-left: 10px;">
                        List Result:
                        <div class="content-notice2">
<textarea cols="50" rows="20">
<?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='table')?'<table id="'.$this->param['table_name'].'_list_table">'.chr(13):'<div id="'.$this->param['table_name'].'_list_div">'.chr(13);?>
<?php
if(isset($this->param['table_type'])&&$this->param['table_type']=='div')
{
    if(  $_->param['trid_prefix'] )
        $tr = '<div id="'.$_->param['trid_prefix'].'" class="list_tr">'.chr(13);
    else
        $tr = '<div class="list_tr">'.chr(13);
    $td = 'div';
    $ttr = 'div';
    $endtr = '</div>'.chr(13);
}
else
{
    if(  $_->param['trid_prefix'] )
        $tr = '<tr id="'.$_->param['trid_prefix'].'" class="list_tr">'.chr(13);
    else
        $tr = '<tr class="list_tr">'.chr(13);
    $td = 'td';
    $ttr = 'tr';
    $endtr = '</tr>'.chr(13);
}
?>
        <?php echo $tr;?>
<?php
foreach($this->param['tablestr'] as $eachrow)
{
    echo '              <'.$td.(isset($_->param['css_prefix'])?' class="'.$_->param['css_prefix'].$eachrow['field'].'"':'').'>'.$eachrow['showname'].'</'.$td.'>'.chr(13);
}
echo $extcol;
?>
        <?php echo $endtr;?>
    <?php echo '<?php foreach('.$this->param['php_prefix'].' as $k=>$v){ ?>'.chr(13);?>
        <?php
if(  $_->param['trid_prefix'] )
    $tr = '<'.$ttr.' id="'.$_->param['trid_prefix'].'<?php echo $k;?>" class="list_tr">'.chr(13);
else
    $tr = '<'.$ttr.' class="list_tr">'.chr(13);
echo $tr;?>
<?php
foreach($this->param['tablestr'] as $eachrow)
{
    echo '              <'.$td.(isset($_->param['css_prefix'])?' class="'.$_->param['css_prefix'].$eachrow['field'].'"':'').'>'.chr(13).'                   <?php echo $v[\''.$eachrow['colname'].'\'];?>'.chr(13).'              </'.$td.'>'.chr(13);
}
echo $extcol;
?>
        <?php echo $endtr;?>
    <?php echo '<?php } ?>'.chr(13);?>
<?php echo (isset($this->param['table_type'])&&$this->param['table_type']=='table')?'</table>'.chr(13):'</div>'.chr(13);?>
</textarea>
                        </div>
                    </div>

<!--Action Template:-->

<div class="content-notice" style="float: left;width:600px;margin-left: 10px;">
Action Template:
<div class="content-notice2">
<textarea cols="80" rows="20">
&lt;?php
//require_once __DIR__.'/../common/<?php echo isset($this->param['target_app'])?$this->param['target_app']:'_app_';?>_<?php echo $folderandaction[0];?>_common.php';
require_once J7SYS_EXTENSION_DIR . '/lib3rd/arrayHelp.php';
require_once J7SYS_EXTENSION_DIR . '/lib/AdminPanelListHelper.class.php';

class <?php echo isset($this->param['target_app'])?$this->param['target_app']:'_app_';?>_<?php echo $folderandaction[0];?>_<?php echo $folderandaction[1];?>_Action extends <?php echo isset($this->param['target_app'])?$this->param['target_app']:'_app_';?>_<?php echo $folderandaction[0];?>_common
{
	public $id;
	public $input = array();
	public $item = array();

	public $rtypes = array(
		1=>'mock',
		2=>'action',
		3=>'url',
		4=>'mockdata_a'
	);

	public $link = '';
	/**
	 * @var AdminPanelListHelper $_aplh
	 */
	public $_aplh;

	public function __j7construct()
	{
		parent::__j7construct();
		$this->link = $this->rewrite_base.'<?php echo isset($this->param['target_name'])?$this->param['target_name']:'';?>';
		$this->_aplh = new AdminPanelListHelper($this->ps);
	}

	public function execute()
	{
		$this->cond = $this->cond?array_filter($this->cond,function($v){if($v!==''){return true;}}):$this->cond;
		$this->ps = $this-><?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service->get<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'';?>s($this->ps,$this->cond);
		$items = $this->ps->getItems();
		if( $items )
		{
//            foreach($items as $k=>$v)
//            {
//                $v['returntype'] = $v['returntype'].' - '.(isset($this->rtypes[$v['returntype']])?$this->rtypes[$v['returntype']]:'');
//            }
//            $this->ps->setItems($items);
		}

        $this->_aplh->set('cond',$this->cond)->set('colshowconfig',array('name'=>array('interface'=>'对外接口')))
            ->set('url',array('edit'=>$this->link.'~edit?id=','add'=>$this->link.'~add','show'=>null,'delete'=>$this->link.'~delete?id=')
            );
	}

	public function add()
	{
	}

	public function edit()
	{
		$this->item = $this-><?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service->get<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'';?>ByPK($this->id);
	}

	public function post()
	{
		if( isset($this->input['created_at']) )
			unset($this->input['created_at']);
		if( isset($this->input['id']) && $this->input['id'] )
		{
			$this-><?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service->update<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'';?>($this->input);
		}
		else
		{
			$this-><?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service->add<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'';?>($this->input);
		}
		return $this->redirect($this->link);
	}

	public function delete()
	{
		$this-><?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service->delete<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'';?>ByPK($this->id);
		return $this->redirect($this->link);
	}

}
</textarea>
</div>
</div>







<!--View Template:-->

<div class="content-notice" style="float: left;width:320px;margin-left: 10px;">
View Template:
<div class="content-notice2">
<textarea cols="40" rows="20">
<style>
    input[type=text]{width: 400px;}
    textarea{width: 400px;height: 120px;}
</style>
    

<div  class='content-notice'>
    <a href="&lt;?php echo $this->link;?&gt;" class="button">返回列表</a>
</div>
<div  class='content-notice'>
&lt;?php
switch ($this->__runingMethod)
{
    case 'add':
    case 'edit':
        ?&gt;

        &lt;?php
        break;
    default:
        ?&gt;
        <div>
            &lt;?php
            $this->_aplh->export();
            ?&gt;
        </div>
        &lt;?php
        break;
}
?&gt;
</div>

</textarea>
</div>
</div>







<!--Service Template:-->

<div class="content-notice" style="float: left;width:320px;margin-left: 10px;">
Service Template:
<div class="content-notice2">
<textarea cols="40" rows="20">

class <?php echo isset($this->param['target_service'])?$this->param['target_service']:'';?>Service extends baseServiceClass
{
    /**
     * @return J7Page
     */
    public function get<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'_XXX';?>s($ps,$cond=[])
    {
        return $this->pager('<?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?>', $ps,$cond);
    }
    public function get<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'_XXX';?>ByPK($id)
    {
        return $this-><?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?>DbDAO->getByPk($id);
    }
    public function delete<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'_XXX';?>ByPK($id)
    {
        return $this-><?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?>DbDAO->deleteByPk($id);
    }
    public function add<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'_XXX';?>($input)
    {
        return $this-><?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?>DbDAO->add($input);
    }
    public function update<?php echo isset($this->param['target_servicemethod'])?$this->param['target_servicemethod']:'_XXX';?>($input)
    {
        return $this-><?php echo isset($this->param['target_dao'])?$this->param['target_dao']:'';?>DbDAO->updateByPk($input);
    }
}
</textarea>
</div>
</div>







                    <div style="clear: both;"></div>
                </div>
                <?php
            }
        }
        ?>
        </form>
    </div>
</div>
