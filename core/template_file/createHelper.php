<?php
class createHelper
{
	public function fileTemplate($act,$param,$mparam=[])
	{
		if( $act )
		{
			if( isset($mparam['_reset']) )
				$param['_reset']=$mparam['_reset'];

			$data['msg'] = [];
			switch($act)
			{
                case 'app':
                    $mparam['name'] = trim($mparam['name'],' \/.-|');
                    $appdir = J7Config::instance()->getAppDir($mparam['name']);

                    if( !is_dir( $appdir ) )
                    {
                        mkdir( $appdir ) ;
                    }
                    if( !is_dir( $appdir.'/common' ) )
                    {
                        mkdir( $appdir.'/common' ) ;
                    }
                    $path = $appdir.'/common';
                    $fname = $path.'/'.$mparam['name'].'_common.php';

                    if( !file_exists($fname) || $param['_reset'] )
                    {
                        $filestr = '<?php
require_once J7SYS_CLASS_DIR.\'/basichelp/'.$mparam['name'].'_basic_action.php\';
class '.$mparam['name'].'_common extends '.$mparam['name'].'_basic_action {}';
                        file_put_contents($fname,$filestr);
                        $data['msg'][] = '成功生成app根文件: '.$fname;
                        $data['msg'][] = '注意文件同步:'.$fname.'文件';
                        $this->flushAppBasicAction($mparam['name']);
                    }
                    else
                    {
                        $data['msg'][] = 'app根文件已存在: '.$fname;
                    }
                    break;
				case 'dao':
					if( isset($mparam['name']) )
						$param['dao_name']=$mparam['name'];
					if( isset($mparam['pk']) )
						$param['dao_pkname']=$mparam['pk'];
					if( isset($mparam['app']) )
						$param['dao_rootapp']=$mparam['app'];
					if( isset($mparam['table']) )
						$param['dao_tablename']=$mparam['table'];
					if( isset($mparam['class']) )
						$param['dao_rootclass']=$mparam['class'];
					if( isset($mparam['db']) ) //数据库链接id
						$param['dao_db']=$mparam['db'];

					$param['dao_name'] = isset($param['dao_name'])?$param['dao_name']:'';
					$param['dao_tablename'] = isset($param['dao_tablename'])?$param['dao_tablename']:'';
					$param['dao_rootapp'] = isset($param['dao_rootapp'])?$param['dao_rootapp']:null;

					if( !isset($param['dao_db']) || is_null($param['dao_db']) )
					{
                        if( empty($param['dao_name']) )
                            return '不设置DB则必须设置dao名';
					}
					else
                    {
                        $dbinfos = J7Config::instance($param['dao_rootapp'])->get('db','db/data_source.php');
                        if( !isset($dbinfos[$param['dao_db']]) )
                        {
                            return '没有对应的db链接信息';
                        }
                        $_dbconinfo = $dbinfos[$param['dao_db']];
                    }

					$_dao_name_s = explode(',',$param['dao_name']);
					$_dao_tablename_s = explode(',',$param['dao_tablename']);
					if( count($_dao_name_s)>1 || count($_dao_tablename_s)>1 )
					{
						if( !empty($param['dao_name']) && !empty($param['dao_tablename']) && count($_dao_tablename_s)!=count($_dao_name_s) )
							return 'DAO名与table名个数必须一致';
						$maxd = max(count($_dao_name_s),count($_dao_tablename_s));

						for ($kkk=0;$kkk<$maxd;$kkk++ )
						{

							$t = $param;
							$t['dao_name'] = isset($_dao_name_s[$kkk])?$_dao_name_s[$kkk]:$param['dao_name'];
							$t['dao_tablename'] = isset($_dao_tablename_s[$kkk])?$_dao_tablename_s[$kkk]:$param['dao_tablename'];
							$trr = $this->fileTemplate('dao',$t);

							if( is_string($trr) )
								$data['msg'][] = $trr;
							else
								$data['msg'] = array_merge($data['msg'],$trr['msg']);
						}
						return $data;
					}
					if( empty($param['dao_name']) && empty($param['dao_tablename']) )
						return 'dao / table 必须设置一个';

					$this->checkapp($param['dao_rootapp']);

                    if( !isset($param['dao_rootclass']) )
                    {
                        $param['dao_rootclass'] = 'DbCrudDAO';
                    }

					if( $param['dao_rootclass'] == 'RedisCrudDAO' || $param['dao_rootclass'] == 'RedisSimpleDAO' )
						$param['dao_extname'] = 'RedisDAO';
                    elseif ( $param['dao_rootclass'] == 'DAO' )
                        $param['dao_extname'] = 'DAO';
                    else
                        $param['dao_extname'] = 'DbDAO';


					if( empty( $param['dao_tablename']) )
						$param['dao_tablename'] = $param['dao_name'];
					elseif( empty( $param['dao_name']) )
					{
						$p = explode('_',$param['dao_tablename']);
						foreach ($p as $ep)
						{
							$param['dao_name'] .= empty($param['dao_name'])?$ep:(empty($ep)?$ep:(strtoupper(substr($ep,0,1)).substr($ep,1)));
						}
					}

                    $isQueryDb=false;
					if( isset($_dbconinfo) && $_dbconinfo )
                    {
                        if( !isset($param['dao_pkname']) || empty($param['dao_pkname']) )
                        {
                            $dbconnection = $this->getDb($param['dao_db'],$param['dao_rootapp']);
                            $sql1 = 'SHOW FULL COLUMNS FROM `'.$param['dao_tablename'].'`';
                            $_tmp_columns = $dbconnection->query($sql1)->fetchAll();
                            if( empty($_tmp_columns) )
                                return '没有表结构';
                            foreach ($_tmp_columns as $ecolumns)
                            {
                                if( strtoupper($ecolumns['Key']) == 'PRI' )
                                {
                                    $param['dao_pkname'] = $ecolumns['Field'];
                                    break;
                                }
                            }
                            if( !isset($param['dao_pkname']) || empty($param['dao_pkname']) )
                                $param['dao_pkname'] = $_tmp_columns[0]['Field'];

                            $isQueryDb=true;
                        }
                        $param['dao_pks'] = explode(',',str_replace(' ','',trim($param['dao_pkname'],',')));

                        if( !isset($mparam['map']) || empty($mparam['map']) )
                        {
                            $_data_map =  $param['dao_tablename'];
                            $dbrename = J7Config::instance($param['dao_rootapp'])->get('table_map_rename','db/map.php');

                            if( $dbrename && isset($dbrename[$param['dao_db']]) && isset($dbrename[$param['dao_db']][$param['dao_tablename']]) )
                                $_data_map = $dbrename[$param['dao_db']][$param['dao_tablename']];
                            if( isset($_dbconinfo['j7map_perfix']) )
                                $_data_map = $_dbconinfo['j7map_perfix'].$_data_map;
                            if( $_data_map !=$param['dao_tablename'] )
                            {
                                $param['map'] = $_data_map;
                            }
                        }
                    }


					$filestr = '';
					ob_start();
					require( J7SYS_CORE_DIR . '/template_file/dao.php');
					$filestr = ob_get_clean();

					if( isset($param['dao_rootapp']) && $param['dao_rootapp'] ){
						if( !is_dir( J7Config::instance()->getAppDir($param['dao_rootapp']).'/__dao/') )
							mkdir( J7Config::instance()->getAppDir($param['dao_rootapp']).'/__dao/');
						$fname = J7Config::instance()->getAppDir($param['dao_rootapp']).'/__dao/'.$param['dao_name'].$param['dao_extname'].'.php';
					}
					else
						$fname = J7SYS_DAO_DIR . DIRECTORY_SEPARATOR .$param['dao_name'].$param['dao_extname'].'.php';

					$data['msg'][] = '文件位置: '.$fname;

					if( is_readable($fname) && !$param['_reset'] )
					{
						$data['msg'][] = 'dao_name_文件已存在';
						break;
					}

					if( $isQueryDb && isset($param['dao_tablename']) && isset($param['dao_db']) )
                    {
                        $this->showContent = 4;
                        $this->daomap($param['dao_db'],isset($param['dao_rootapp'])?$param['dao_rootapp']:null,$param['dao_tablename']);   //__basic__
                    }

					file_put_contents($fname,$filestr);
					$this->changeflag($fname);


                    if( isset($param['dao_rootapp']) && $param['dao_rootapp'] )
                    {
                        $this->flushAppBasicAction($param['dao_rootapp']);
                        $this->flushAppBasicService($param['dao_rootapp']);
                    }
                    else
                    {
                        $this->flushRootBasicAction();
                        $this->flushRootBasicService();
                    }

					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					//echo $filestr;
					echo $fname.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;

					$data['msg'][] = '注意文件同步:'.$fname.'文件';
					break;
				case 'service':
                    $param['service_dao'] = '';
                    $param['service_rootapp'] = '';
					if( isset($mparam['name']) )
						$param['service_name']=$mparam['name'];
					if( isset($mparam['daos']) )
						$param['service_dao']=$mparam['daos'];
					if( isset($mparam['app']) )
						$param['service_rootapp']=$mparam['app'];

					$this->checkapp($param['service_rootapp']);

					if( $param['service_name']=='' )
					{
						$data['msg'][] = 'service_name不能为空';
						break;
					}
					if( substr($param['service_dao'],-7,7)=='Service' )
						$param['service_dao'] = substr($param['service_dao'],0,-7);

					// $param['service_name'] = strtoupper(substr($param['service_name'], 0, 1)).substr($param['service_name'], 1, strlen($param['service_name'])-1);

                    if( substr($param['service_name'],-7,7)=='Service' )
                        $param['service_name'] = substr($param['service_name'],0,strlen($param['service_name'])-7);

					if( isset($param['service_dao']) && $param['service_dao'] )
					{
						$param['service_dao_split'] = array_unique( explode(',',trim($param['service_dao'],',')) );
						if( $param['service_dao_split'] )
                        {
                            foreach ( $param['service_dao_split'] as $ek=>$v )
                            {
                                if( substr($v,-5,5)!=='DbDAO' && substr($v,-5,5)!=='RedisDAO' )
                                    $param['service_dao_split'][$ek] = $v.'DbDAO';
                            }
                        }
					}

					ob_clean();
					require( J7SYS_CORE_DIR . '/template_file/service.php');
					$filestr = ob_get_clean();

					if( isset($param['service_rootapp']) && $param['service_rootapp'] ){
						if( !is_dir( J7Config::instance()->getAppDir($param['service_rootapp']).'/__service/') )
							mkdir( J7Config::instance()->getAppDir($param['service_rootapp']).'/__service/');
						$fname = J7Config::instance()->getAppDir($param['service_rootapp']).'/__service/'.$param['service_name'].'Service.php';
					}
					else
					{
						$fname = J7SYS_SERVICE_DIR . DIRECTORY_SEPARATOR .$param['service_name'].'Service.php';
					}
					$data['msg'][] = '文件位置: '.$fname;

					if( is_readable($fname) && !$param['_reset'] )
					{
						$data['msg'][] = 'service_name_文件已存在';
						return $data;
					}

					file_put_contents($fname,$filestr);
					$this->changeflag($fname);

                    if( isset($param['service_rootapp']) && $param['service_rootapp'] )
                    {
                        $this->flushAppBasicAction($param['service_rootapp']);
                        $this->flushAppBasicService($param['service_rootapp']);
                    }
                    else
                    {
                        $this->flushRootBasicAction();
                        $this->flushRootBasicService();
                    }

					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo $filestr.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;

					$data['msg'][] = '注意文件同步:'.$fname.'文件';
					break;
				case 'appfolder':

					if( isset($mparam['name']) )
						$param['folder_name']=$mparam['name'];
					if( isset($mparam['app']) )
						$param['folder_rootapp']=$mparam['app'];
					if( isset($mparam['class']) )
						$param['folder_rootclass']=$mparam['class'];
					else
						$param['folder_rootclass']=$param['folder_rootapp'].'_common';

					$this->checkapp($param['folder_rootapp']);

					if( $param['folder_name']=='' || $param['folder_rootapp']=='' )
					{
						$data['msg'][] = 'name/app [folder_name,folder_rootapp] 不能为空';
						break;
					}
					if( is_dir( J7Config::instance()->getAppDir($param['folder_rootapp']).'/'.$param['folder_name']) && !$param['_reset']  )
					{
						$data['msg'][] = $param['folder_name'].' 已经存在';
						break;
					}
					//生成文件夹
                    if( !file_exists(J7Config::instance()->getAppDir($param['folder_rootapp']).'/'.$param['folder_name']) )
					    mkdir( J7Config::instance()->getAppDir($param['folder_rootapp']).'/'.$param['folder_name'] );
					$this->changedirflag( J7Config::instance()->getAppDir($param['folder_rootapp']).'/'.$param['folder_name']);

					//生成文件
                    if(  ob_get_status() )
                        ob_end_flush();
                    ob_start();
					require( J7SYS_CORE_DIR . '/template_file/folder_common.php');
					$filestr = ob_get_clean();
					$fname = J7Config::instance()->getAppDir($param['folder_rootapp']).'/common/'.$param['folder_rootapp'].'_'.$param['folder_name'].'_common.php';
					file_put_contents($fname,$filestr);
					$this->changeflag($fname);

					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo $filestr.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;

					$data['msg'][] = '文件位置: '.$fname;
					$data['msg'][] = '注意文件同步:'.$fname.'文件';
					break;
				case 'action':
                    $param['action_service'] = '';

					if( isset($mparam['name']) )
						$param['action_name']=$mparam['name'];
					if( isset($mparam['app']) )
						$param['action_app']=$mparam['app'];
					if( isset($mparam['module']) )
						$param['action_folder']=$mparam['module'];
					if( isset($mparam['service']) )
						$param['action_service']=$mparam['service'];
					if( isset($mparam['return']) )
						$param['action_return']=$mparam['return'];

					$this->checkapp($param['action_app']);

					if( !isset($param['action_name']) || !isset($param['action_folder']) || $param['action_name']=='' || $param['action_folder']=='' )
					{
						$data['msg'][] = 'name / module 不能为空';
						break;
					}

                    if( !isset($param['action_return']) || !$param['action_return'] )
                    {
                        $data['msg'][] = 'return 不能为空,可以return=view|json|jsonp 等等,view会新增一个view文件在同目录';
                        break;
                    }
					//生成文件

					$param['action_service_split'] = array_unique( explode(',',trim($param['action_service'],',')) );
                    foreach ( $param['action_service_split'] as $ek=>$v )
                    {
                        if( strtolower(substr($v,-7,7))=='service' )
                            $param['action_service_split'][$ek] = substr($v,0,-7).'Service';
                        elseif( substr($v,-7,7)!=='Service' )
                            $param['action_service_split'][$ek] = $v.'Service';
                    }

                    if(  ob_get_status() )
                        ob_end_flush();
					ob_start();
					require( J7SYS_CORE_DIR . '/template_file/action.php');
					$filestr = ob_get_clean();
					$fname = J7Config::instance()->getAppDir($param['action_app']).'/' .$param['action_folder'].'/' .$param['action_name'].'Action.php';

					if( is_readable($fname) && (!isset($param['_reset']) || $param['_reset']) )
					{
						$data['msg'][] = 'action_name_文件已存在';
						break;
					}

					file_put_contents($fname,$filestr);
					$this->changeflag($fname);

					echo '********************************************************'.PHP_EOL;
					echo '********************************************************'.PHP_EOL;
					echo $filestr.PHP_EOL;

					$data['msg'][] = '文件位置 :'.$fname;
					$data['msg'][] = '注意文件同步:'.$fname.'文件';

					if( strpos($param['action_return'],'view') !== false  ){
						$fnamev = J7Config::instance()->getAppDir($param['action_app']).'/' .$param['action_folder'].'/' .$param['action_name'].'.php';

						if( !is_dir( J7Config::instance()->getAppDir($param['action_app']).'/') )
						{
							mkdir( J7Config::instance()->getAppDir($param['action_app']).'/');
							$this->changedirflag(J7Config::instance()->getAppDir($param['action_app']).'/');
						}
						if( !is_dir( J7Config::instance()->getAppDir($param['action_app']).'/'.$param['action_folder']) )
						{
							mkdir( J7Config::instance()->getAppDir($param['action_app']).'/'.$param['action_folder']);
							$this->changedirflag(J7Config::instance()->getAppDir($param['action_app']).'/'.$param['action_folder']);
						}

						//$fileinv = 'SuccessCreateFile:'.$param['action_name'];
						ob_start();
						require( J7SYS_CORE_DIR . '/template_file/action_view.php');
						$fileinv = ob_get_clean();

						file_put_contents($fnamev,$fileinv);
						$this->changeflag($fname);
						echo '********************************************************'.PHP_EOL;
						echo $fileinv.PHP_EOL;
						echo '********************************************************'.PHP_EOL;

						$data['msg'][] = 'View视图文件位置:'.$fnamev;
						$data['msg'][] = '注意文件同步:'. $fnamev .'文件';

						$data['msg'][] = '------>:: http://__domain__/'.$param['action_app'].'.php/'.$param['action_folder'].'/'.$param['action_name'];
					}
					echo '********************************************************'.PHP_EOL;

					break;
				default:
					break;
			}

			return $data;
		}
	}

	protected function changeflag($fname)
	{
		chmod($fname,0766);
//        $cmd = 'cd '.rtrim($_SERVER['DOCUMENT_ROOT'],'web').';svn add * --force;';
//        exec($cmd,$r);
	}

	protected function changedirflag($flagTopath)
	{
//        $flagTopath = rtrim(ltrim($flagTopath,'/'),'/');
//        $cmd = 'cd '.rtrim($_SERVER['DOCUMENT_ROOT'],'web').';svn add '.$flagTopath;
//        exec($cmd,$r);
	}


	public function checkapp($app=null)
	{
		if( $app )
		{
			if( !is_dir(J7Config::instance()->getAppDir($app)) )
				throw new coreException('没有application项目, No application :'.J7Config::instance()->getAppDir($app));
		}
	}

	public function checkDir($path=null)
    {
        if( $path )
        {
            return is_dir($path);
        }
        return false;
    }

/*
 * ************************************************************* DAO ORM MAP
 */

	protected function getDb($k=0,$app=null)
	{
		$dbc = J7Config::instance($app)->get('db','db/data_source.php');
		return J7Factory_Db::factory( $dbc[$k] );
	}

	public $_map_list_check = [];
	public function daomap($dbindex=0,$app=null,$tables='',$ignoreprefix=null)
	{
//	    if( empty($app) )
//	        throw new coreException('必须设置app,全局的话请使用 %');  //__basic__
//        if( $app=='%' )   //__basic__
//            $app = null;

        $this->checkapp($app);

		$db = $this->getDb($dbindex,$app);

        $r = $db->query('show tables')->fetchAll();
        if( $tables )
        {
            $tables_split = explode(',',$tables);
            foreach($r as $row)
            {
                $table = current($row);
                if( in_array($table,$tables_split) )
                    $this->createDaoMap($table,$dbindex,$app);
            }
        }
        else
        {
            foreach($r as $row)
            {
                $table = current($row);
                if( empty($ignoreprefix) || strpos($table,$ignoreprefix)!==0 )
                    $this->createDaoMap($table,$dbindex,$app);
            }
        }
	}

	public $showContent=false;
	public function createDaoMap($table,$dbindex,$app=null)
	{
		$dbtc = J7Config::instance($app)->get('table_references','db/table_references.php');
		$dbc = J7Config::instance($app)->get('db','db/data_source.php');

		$db = $this->getDb($dbindex,$app);

		$_tmp_config = (isset($dbtc[$dbindex])&&isset($dbtc[$dbindex][$table]))?$dbtc[$dbindex][$table]:null;  //关联数据使用
		$_tmp_name = $table;

		//重新命名
		$dbrename = J7Config::instance($app)->get('table_map_rename','db/map.php');
		if( $dbrename && isset($dbrename[$dbindex]) && isset($dbrename[$dbindex][$table]) )
			$_tmp_name = $dbrename[$dbindex][$table];

		//表前缀
		if( isset($dbc[$dbindex]['j7map_perfix']) )
			$_tmp_name = $dbc[$dbindex]['j7map_perfix'].$_tmp_name;

		//基类
        $baseClass = 'J7Data';
        $mapbaseClass = J7Config::instance($app)->get('table_map_baseclass','db/map.php');
        if( $mapbaseClass && isset($mapbaseClass[$dbindex]) && isset($mapbaseClass[$dbindex]['baseClass']) )
            $baseClass = $mapbaseClass[$dbindex]['baseClass'];
        if( $mapbaseClass && isset($mapbaseClass[$dbindex]) && isset($mapbaseClass[$dbindex][$table]) )
            $baseClass = $mapbaseClass[$dbindex][$table];

		$sql1 = 'SHOW FULL COLUMNS FROM `'.$table.'`';
		$_tmp_columns = $db->query($sql1)->fetchAll();

		ob_start();
		ob_clean();
		require( J7SYS_CORE_DIR.'/template_file/dao.class.php');
		$filestr = ob_get_clean();

        if( !is_dir( J7SYS_CLASS_DIR ) )
            mkdir( J7SYS_CLASS_DIR );
        if( !is_dir( J7SYS_CLASS_DIR.'/daomap') )
            mkdir( J7SYS_CLASS_DIR.'/daomap' );

		if( $app )
		{
            if( !is_dir( J7SYS_CLASS_DIR.'/daomap/'.$app) )
                mkdir( J7SYS_CLASS_DIR.'/daomap/'.$app );
			$tofile = J7SYS_CLASS_DIR.'/daomap/'.$app.'/j7data_'.$_tmp_name.'.class.php';
		}
		else
		{
			$tofile = J7SYS_CLASS_DIR.'/daomap/j7data_'.$_tmp_name.'.class.php';
		}

		if( in_array($tofile,$this->_map_list_check) )
        {
            foreach ( $this->_map_list_check as $ef )
                unlink($ef);
            echo '本次生成map发现重复命名的目标文件名/对象名/表名'.PHP_EOL;
            echo 'tofile : '.$tofile .PHP_EOL;
            echo 'table : '.$table .PHP_EOL;
            echo '可以在 **/config/db/map.php配置别名 、或者在数据库连接配置里配置j7map_perfix批量重命名';
            throw new Exception('!');
        }

        $this->_map_list_check[] = $tofile;
		file_put_contents($tofile,$filestr);

		if( $this->showContent )
		{
			if( $this->showContent==2 )
			{
				echo $filestr.PHP_EOL;
				echo 'ok____'.$app.' : '.$table.' : '.$_tmp_name.PHP_EOL;
			}
			elseif( $this->showContent==1 && $_tmp_config )
			{
				echo $filestr.PHP_EOL;
				echo 'ok____'.$app.' : '.$table.' : '.$_tmp_name.PHP_EOL;
			}
            elseif( $this->showContent==3 )
            {
                echo 'ok____'.$app.' : '.$table.' : '.$_tmp_name.PHP_EOL;
            }
            elseif( $this->showContent==4 )
            {
                echo '********************************************************'.PHP_EOL;
                echo '********************************************************'.PHP_EOL;
                echo '生成ActiveRecord辅助文件 '.$tofile.PHP_EOL;
            }
			elseif (is_array($this->showContent) && in_array($table,$this->showContent))
			{
				echo $filestr.PHP_EOL;
				echo 'ok____'.$app.' : '.$table.' : '.$_tmp_name.PHP_EOL;
			}
		}
		$this->updateArVersionConfig($_tmp_name,$tofile);
	}

	public $arconfigold=null;
    public $arconfignew=null;
    public $arconfig=null;
	public function updateArVersionConfig($_tmp_name,$tofile)
    {
        if( $this->arconfignew===null )
            $this->arconfignew = $this->arconfigold = config('ar_version_keep','db/ar_version_keep.php');
        if( $this->arconfig===null )
            $this->arconfig = config('ar_version','db/ar_version.php');
        if( isset($this->arconfignew[$_tmp_name]) && isset($this->arconfignew[$_tmp_name]['md5']) && $this->arconfignew[$_tmp_name]['md5']==md5_file($tofile) )
        {

        }
        else
        {
            $version = (isset($this->arconfignew[$_tmp_name]) && isset($this->arconfignew[$_tmp_name]['v']))?$this->arconfignew[$_tmp_name]['v']:0;
            $this->arconfignew[$_tmp_name] = ['md5'=>md5_file($tofile),'v'=>intval($version)+1,'d'=>date('Y-m-d H:i:s')];
            $this->arconfig[$_tmp_name] = intval($version)+1;
        }
        if( !isset($this->arconfig[$_tmp_name]) && isset($this->arconfignew[$_tmp_name]) )
            $this->arconfig[$_tmp_name] = $this->arconfignew[$_tmp_name]['v'];
    }

    public function writeArVersionConfig()
    {
        if( $this->arconfigold != $this->arconfignew )
        {
            $this->arconfignew['_j7c'] = date('Y-m-d H:i:s');
            file_put_contents(J7SYS_CONFIG_DIR.'/db/ar_version_keep.php','<?php $J7CONFIG[\'ar_version_keep\'] = '.var_export($this->arconfignew,true).';');

            $min = min($this->arconfig);
            foreach ($this->arconfig as $k=>$v)
            {
                if( $v==$min )
                    unset($this->arconfig[$k]);
            }
            $this->arconfig['_j7c'] = $min;
            file_put_contents(J7SYS_CONFIG_DIR.'/db/ar_version.php','<?php $J7CONFIG[\'ar_version\'] = '.var_export($this->arconfig,true).';');
        }
    }

	public function explainCond(&$data,$k)
	{
		if( is_array($data) )
		{
			$cond = [];
			foreach($data as $ek=>$ep){
				if( substr($ep,0,1)=='?' )
				{
					$cond[] = '\''.$ek.'\' =>$this->__data[\''.substr($ep,1).'\']';
				}
				elseif( substr($ep,0,1)=='$' )
				{
					$cond[] = '\''.$ek.'\' =>'.$ep;
				}
				elseif( is_numeric($ep) )
				{
					$cond[] = '\''.$ek.'\' =>'.$ep;
				}
				else
				{
					$cond[] = '\''.$ek.'\' =>\''.$ep.'\'';
				}
			}
			$data = '['.implode(',',$cond).']';
		}
		elseif( is_scalar($data) )
		{
			if( substr($data,0,1)=='?' )
			{
				$data = '$this->__data[\''.substr($data,1).'\']';
			}
			elseif( substr($data,0,1)=='$' )
			{
			}
			elseif( is_numeric($data) )
			{
			}
			else
			{
				$data = '\''.$data.'\'';
			}
		}
	}


    /**
     * *******************************
     * basic_action / basic_service 基本结构
     */


    public $fileContentStart = '<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请勿修改此文件,系统会自动刷这个文件!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */';

    public function flushAppCheckDir()
    {
        if( !is_dir( J7SYS_CLASS_DIR.'/basichelp') )
            mkdir( J7SYS_CLASS_DIR.'/basichelp' );
        return J7SYS_CLASS_DIR.'/basichelp';
    }
    public function flushAppBasicService($appname)
    {
        $apppath = J7Config::instance()->getAppDir($appname);
        $apppath = rtrim($apppath,DIRECTORY_SEPARATOR);
        $basichelp = $this->flushAppCheckDir();

        $actionContent = '';
        $serviceclass = $appname.'_basic_service';
        $servicefile = $basichelp.DIRECTORY_SEPARATOR.$serviceclass.'.php';

        $actionContent = $this->fileContentStart.'
class '.$appname.'_basic_service extends basic_service
{';
        //读取本app的service文件
        $actionContent .= $this->getObjList($apppath.DIRECTORY_SEPARATOR.'service','\\j7f\\'.$appname.'\\','Service.php',false,[]);
        $actionContent .= $this->getObjList($apppath.DIRECTORY_SEPARATOR.'dao','\\j7f\\'.$appname.'\\','DAO.php',false,[]);
        $actionContent.='
}';

        file_put_contents($servicefile,$actionContent);
    }

    public function flushAppBasicAction($appname)
    {
        $apppath = J7Config::instance()->getAppDir($appname);
        $apppath = rtrim($apppath,DIRECTORY_SEPARATOR);
        $basichelp = $this->flushAppCheckDir();

        $actionclass = $appname.'_basic_action';
        $actionfile = $basichelp.DIRECTORY_SEPARATOR.$actionclass.'.php';

        $actionContent = $this->fileContentStart.'
class '.$appname.'_basic_action extends basic_action
{';
        //读取本app的service文件
        $actionContent .= $this->getObjList($apppath.DIRECTORY_SEPARATOR.'service','\\j7f\\'.$appname.'\\','Service.php');

        $callDaoInAction = J7Config::instance()->loadConfigFileWithApp($appname,'app.php');


        if( $callDaoInAction && isset($callDaoInAction['_j7_system_allow_call_dao_in_action']) && $callDaoInAction['_j7_system_allow_call_dao_in_action'] )
        {
            $actionContent .= $this->getObjList($apppath.DIRECTORY_SEPARATOR.'dao','\\j7f\\'.$appname.'\\','DAO.php');
            $actionContent .= $this->getObjList(J7SYS_DAO_DIR,'','DAO.php',false,[]);
        }

        $actionContent.='
}';

        file_put_contents($actionfile,$actionContent);
    }

    public function flushRootBasicAction()
    {
        $baseClass = config('_j7_system_basic_class_action');
        $baseClass = $baseClass?:'Action';
        $basichelp = $this->flushAppCheckDir();
        //根目录下的basic_action
        $actionContent = $this->fileContentStart.'
class basic_action extends '.$baseClass.'
{';
        $serviceclass = 'basic_action';
        $servicefile = $basichelp.DIRECTORY_SEPARATOR.$serviceclass.'.php';
        $actionContent .= $this->getObjList(J7SYS_SERVICE_DIR,'','Service.php',false,[]);

        $callDaoInAction = config('_j7_system_allow_call_dao_in_action',null,true);
        if( $callDaoInAction===true )
            $actionContent .= $this->getObjList(J7SYS_DAO_DIR,'','DAO.php',false,[]);


        $actionContent.='
}';
        file_put_contents($servicefile,$actionContent);
    }

    public function flushRootBasicService()
    {
        $baseClass = config('_j7_system_basic_class_service');
        $baseClass = $baseClass?:'baseServiceClass';

        $basichelp = $this->flushAppCheckDir();

        //根目录下basic_service
        $actionContent = $this->fileContentStart.'
class basic_service extends '.$baseClass.'
{';
        $serviceclass = 'basic_service';
        $servicefile = $basichelp.DIRECTORY_SEPARATOR.$serviceclass.'.php';
        $actionContent .= $this->getObjList(J7SYS_SERVICE_DIR,'','Service.php',false,[]);
        $actionContent .= $this->getObjList(J7SYS_DAO_DIR,'','DAO.php',false,[]);
        $actionContent.='
}';
        file_put_contents($servicefile,$actionContent);
    }


    public function getObjList($dir,$prenamespace='\\',$checkFtype='DAO.php',$retArr=false,$skipName=[])
    {
        $arr = [];
        $actionContent = '';
        if( is_dir($dir) )
        {
            $serviceDir = dir($dir);
            while ($entry = $serviceDir->read()) {
                if (substr($entry, 0, 1) == '.' || substr($entry, 0, 1) == '_') continue;
                if( substr($entry,-strlen($checkFtype))== $checkFtype )
                {
                    $sname = substr($entry,0,-4);
                    if( !in_array($sname,$skipName) )
                    {
                        $actionContent .= '
    /**
     * @var '.$prenamespace.$sname.'
     */
    protected $'.$sname.';';
                        $arr[] = $sname;
                    }
                }
            }
        }
        if( $retArr )
            return $arr;
        else
            return $actionContent;
    }

}