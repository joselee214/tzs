<?php
require_once(__DIR__ ."/../../core/coreFilterAbstract.php");
require_once(__DIR__."/../../core/J7Config.php");
require_once(__DIR__."/../../core/RuntimeData.php");
require_once(__DIR__ . "/../../core/J7Debuger.php");

class SysLogFilter extends coreFilterAbstract
{
    protected $time_start;
    protected $timepassarr=[];
    protected $mempassarr=[];
	public function routeStartup($ret=null)
	{
        $this->timepassarr['routeStartup'] = microtime(true);
        $this->mempassarr['routeStartup'] = memory_get_usage();

        $_dao_mode = config('_j7_system_dao_mode');
        $_syslog_daomode = ( RuntimeData::registry('SYS_ENV') != 'prod' )?'101':'000';
        if( is_string($_dao_mode) )
            $new_dao_mode = $_dao_mode | $_syslog_daomode;
        else
            $new_dao_mode = $_syslog_daomode;
        J7Config::instance()->_replace('_j7_system_dao_mode', $new_dao_mode);
        return $ret;
	}

	public function dispatchShutdown($dispatchReturn)
	{
        /**
         * @var syslogDbDAO $syslogDbDAO
         */
//        $syslogDbDAO = FactoryObject::Get('syslogDbDAO');
//        $d = $syslogDbDAO->_new([]);
//        $t = $d->save();
//
//        var_dump($d);
//
//        var_dump( $t );
//        die;

        $this->timepassarr['dispatchShutdown'] = microtime(true);
        $this->mempassarr['dispatchShutdown'] = memory_get_usage();

		return $dispatchReturn;
    }

	public function endReturn($dispatchReturn)
	{
        $this->timepassarr['endReturn'] = microtime(true);
        $this->mempassarr['endReturn'] = memory_get_usage();

        try
        {

            $isNeedLog = true; //in_array(RuntimeData::registry('SYS_APP'), array('admin','factory','sales'));

            $DoingSql = RuntimeData::get('DoingSql');
            if( isset($DoingSql) && $DoingSql && $isNeedLog)
            {
                foreach ($DoingSql as $item) {
                    list($sql,$rows,$comment) = $this->_buildSql($item);
                    $info = array(
                        'sql'       => $sql,
                        'rows'      => $rows,
                        'comment'   => $comment
                    );
                    debug($sql,'DoingSql : ');
//                    slog($info,'DoingSql');
                }
//                debug(count($DoingSql),'$DoingSQL Count');
            }

            $DoingSql = RuntimeData::get('DoingSql:select');
            if( isset($DoingSql) && $DoingSql && $isNeedLog)
            {
                foreach ($DoingSql as $item) {
                    debug($item,'DoingSql:select');
//                    slog($item,'DoingSql:select');
                }
//                debug(count($DoingSql),'$DoingSQL Count');
            }


//            $DataLog = RuntimeData::get('DataLog');
//            if(false && isset($DataLog) && $DataLog && $isNeedLog ) // 暂时不记录
//            {
//                foreach ($DataLog as $item) {
//                    // do not log for now
//                    if ( in_array($item['param'][0], array('users','user_info'))) {
//                        continue;
//                    }
//                    //debug($item,'DataLog Item');
//                }
//            }
        }
        catch(Exception $e)
        {
            //Record Error!//donohing//
            debug('Record Data Error', 'SqlLogFilter_filter');
        }


        //  debug( round($this->mempassarr['endReturn']/1024,2).'kb - '.round($this->mempassarr['routeStartup']/1024,2).'kb = '.round(($this->mempassarr['endReturn'] - $this->mempassarr['routeStartup'])/1024,2).'kb' , 'Memory: endReturn-routeStartup @url: '.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/N/A') ,'log' );
    }

    /**
     *  记录用不需要转义
     * @param $item
     * @return array
     */
    protected function _buildSql($item) {
        $rows = 0;
        $comment = '';
        $sql = '';
        $table = $item['param'][0];
        switch ($item['sqlmode']) {
            case 'update' :
            case 'updateByPk':
                $sql .= "update `{$table}`";
                if (isset($item['param'][1]) && is_array($item['param'][1])) {
                    $sql .= 'SET ';
                    foreach ($item['param'][1] as $key => $val) {
                        $sql.= "`{$key}`='{$val}'";
                    }
                }

                if (isset($item['param'][2]) ) {
                    if (is_string($item['param'][2])) {
                        $sql .= "WHERE {$item['param'][2]}";
                    }
                }

                $rows = $item['result'];
                break;
            case 'insert':
                $sql = "insert into {$item['param'][0]} (`". implode('`,`' , array_keys($item['param'][1]))."`) VALUES ('".implode('\',\'', $item['param'][1])."')";
                $rows = 1;
                 $comment = 'newId:'. (isset( $item['result']) ? $item['result']: '0');
                break;
            default:

        }

        return array($sql,$rows,$comment);
    }

}