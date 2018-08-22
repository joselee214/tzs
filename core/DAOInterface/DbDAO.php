<?php
require_once __DIR__.'/../J7Data.php';
require_once __DIR__ . '/../Util.php';
require_once __DIR__ . '/../J7Config.php';
require_once __DIR__ . '/../J7Debuger.php';
require_once __DIR__ . '/DBFactory.php';
require_once __DIR__ . '/../J7Exception.php';

//直接操作数据库的基类
abstract class DbDAO extends DAO
{
	/**
	 * 当前 DAO 对应的默认表的主键，多关键字则使用数组。继承类可直接覆写此属性默认值达到设置主关键字的目的。
	 * @var string|array
	 */
	protected $_pk = null;

	/**
	 * @var Adapter_Abstract
	 */
	protected $_dbInstance = null;

	protected $_table = null; //具体表
	protected $_abstable = null; //DAO文件名,用于缓存key
	protected $_data_map=null; //AR对象类
	protected $_dbconinfo = []; //数据库信息
	protected $_dbconindex = null; //连接信息
	protected $_joins = [];
	protected $_inner_joins = [];

	public $_tStructure_dValue = []; //表结构

	public function __construct(){}
	public function __j7construct($dbindex=0)
    {
        $this->__init($dbindex);
    }
	//table指定表  dbindex指定数据库配置
	public function __init($dbindex=0)
	{
		//$this->_abstable = preg_replace('/^([a-zA-Z0-9]+)DbDAO$/', '\1', get_class($this));
		$this->_abstable = substr(get_called_class(),-5,5)=='DbDAO'?substr(get_called_class(),0,-5):get_called_class();
		$_help = explode('\\',$this->_abstable);
		if (empty($this->_table))
		{
			// _table 是没有namespace的绝对字符串
			$this->_table = $_help[count($_help)-1];
		}

		$this->__changeDb($dbindex);
		$this->_changeTable($this->_table);

		if (empty($this->_data_map))
		{
			$this->_data_map =  $this->_table;
			$dbrename = config('table_map_rename','db/map.php');
			if( $dbrename && isset($dbrename[$dbindex]) && isset($dbrename[$dbindex][$this->_table]) )
				$this->_data_map = $dbrename[$dbindex][$this->_table];
			if( isset($this->_dbconinfo['j7map_perfix']) )
				$this->_data_map = $this->_dbconinfo['j7map_perfix'].$this->_data_map;
		}
	}
    
	//外部直接更改DB连接
	protected function __changeDb($dbindex=0)
	{
		$db = config('db');
		$this->_dbconindex = $dbindex?:0;
		if( ( isset($db[$this->_dbconindex]) && $this->_dbconinfo!=$db[$this->_dbconindex] ) )
		{
			$this->_dbconinfo= $db[$this->_dbconindex];
            unset($this->_dbInstance);
		}
		else
		{
			throw new J7Exception('DbDAO : cannot load db config :'.$dbindex);
		}
	}

    public function __get($k)
    {
        if( $k == '_dbInstance' )
        {
            $this->_dbInstance = J7Factory_Db::factory( $this->_dbconinfo );
            return $this->_dbInstance;
        }
        else
        {
            return parent::__get($k);
        }
    }

	protected function __getDbIndex()
	{
		return $this->_dbconindex;
	}

	protected function _changeTable($table)
	{
		$db_prefix = config('db_table_perfix');
		$this->_table = $db_prefix.$table;
	}

	/*
	 * 处理条件赋值
	 */
	protected function _parseWhere($cond,Db_Select $select = null)
	{
		$where = '';
		if (is_array($cond)) {
			foreach ($cond as $field => $value) {
				if (strpos($field, '?') === false) {
					if (preg_match('/^\d$/i', $field))
					{
						#print_r($field);
						$quote  = trim($value);
						if( strtolower(substr($quote,0,3))=="and" )
							$where .= $quote;
						else
							$where .= ' AND '.$quote;
					}
					else
					{
						if( strpos($field,'.')===false )
						{
							$pretable = $this->_table;
							if( $select )
							{
								//对join时候条件自动加表名
								foreach( $select->get_parts('join') as $eachj )
								{
									if( $field == $eachj['cols'] )
									{
										$pretable = $eachj['name'];
										break;
									}
								}
							}
							$text = is_array($value) ? '`'.$pretable.'`.`'.$field.'` IN (?)' : '`'.$pretable.'`.`'.$field.'` = ?';
						}
						else
						{
							$text = is_array($value) ? $field.' IN (?)' : '`'.$field.'` = ?';
						}
						$quote  = trim($this->_dbInstance->quoteInto($text, $value));
						$where .= preg_match('/^OR /i', $field) ? ' '.$quote : ' AND '.$quote;
					}
				}
				else {
					$text = $field;
					if( is_array($value) )
					{
						$quote = [];
						foreach($value as $v)
						{
							if( $v )
								$quote[] = trim($this->_dbInstance->quoteInto($text, $v));
						}
						$quote = $quote?implode(' AND ',$quote):'';
					}
					else
						$quote  = trim($this->_dbInstance->quoteInto($text, $value));
					$where .= preg_match('/^OR /i', $field) ? ' '.$quote : ' AND '.$quote;
				}
			}
			$where = preg_replace('/^(OR|AND)\s+/i', '', trim($where));
		} else if ($cond) {
			$where = strval($cond);
		}
		return $where;
	}

	protected function _select($cond = null, $order = null, $count = null, $offset = null, $cols = null , $returnAr=null)
	{
		$select = $this->_getSelect($this->_table, $cond, $order, $count, $offset, $cols);

        if( is_null($returnAr) )
            $returnAr = true;       //默认获取AR

        if( !$returnAr )
        {
            return $this->_dbInstance->fetchAll($select);
        }
        else
        {
            return $this->_dbInstance->fetchAll($select,null,$this->_getArMapName(),get_called_class());
        }
	}

    /**
     * 返回 ActiveRecord 对象名,不含 j7data , 如果继承返回 null ,则不使用AR
     *
     * @return string
     */
    protected function _getArMapName()
    {
        //如果在namespace下的DAO,需要检查带namespace的map对象类
        $_help = explode('\\',$this->_abstable);
        if( count($_help)>1 )
        {
            array_pop($_help);
            $_map_name =  implode('\\',$_help).'\\j7data_'.$this->_data_map;
        }
        if( !isset($_map_name) || empty($_map_name) || !class_exists($_map_name) )
            $_map_name = 'j7data_'.$this->_data_map;
        return $_map_name;
    }

	protected function _selectOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
	{
		$rows = $this->_select($cond, $order, $count, $offset, $cols, $returnAr);
		return isset($rows[0]) ? $rows[0] : null;
	}

	protected function _getSelect($table, $cond = null, $order = null, $count = null, $offset = null, $cols = null)
	{
		$select = $this->_dbInstance->select();
		if (empty($cols))
			$cols = '*';
		$select->from($table, $cols);
		if (count($this->_joins)) {
			foreach ( $this->_joins as $join ) {
                list($join_table, $join_cond, $join_cols) = $join;
                $select->joinLeft($join_table, $join_cond, $join_cols);
            }
		}
		if (count($this->_inner_joins)) {
			foreach ( $this->_inner_joins as $join ) {
                list($join_table, $join_cond, $join_cols) = $join;
                $select->joinInner($join_table, $join_cond, $join_cols);
            }
		}
		if(strtolower(trim($cols))=="count(*)")
		{
            $cols = strtolower(trim($cols));
			$select->update_parts("cols",array($cols));
		}
		if ($where = $this->_parseWhere($cond,$select))
			$select->where($where);
		if ($order)
			$select->order($order);
		if ($count || $offset)
			$select->limit($count, $offset);
		return $select;
	}

	protected function _getByField($field, $value, $cols = null)
	{
		return $this->_select(array($field=>$value), null, null, null, $cols);
	}

	protected function _getByFieldOne($field, $value, $cols = null)
	{
		$rows = $this->_getByField($field, $value, $cols);
		return isset($rows[0]) ? $rows[0] : null;
	}

	protected function _count($cond)
	{
		$select = $this->_getSelect($this->_table, $cond, null, null, null, 'COUNT(*)');
		return intval($this->_dbInstance->fetchOne($select));
	}

	protected function _updateincr($datas,$cond,$forceString=false)
	{
		return $this->_dbInstance->updateincr($this->_table, $datas, $this->_parseWhere($cond),$forceString);
	}

	//====================
	//DbCrudDAO
	//====================
	protected function _insert($datas)
	{
		return $this->_dbInstance->insert($this->_table, $datas);
	}
	protected function _insertGetLid($datas)
	{
		$rowCount = $this->_dbInstance->insert($this->_table, $datas);
		$rid = $this->_dbInstance->lastInsertId();
		return $rid?:$rowCount;
	}
	protected function _update($datas, $cond = null)
	{
		return $this->_dbInstance->update($this->_table, $datas, $this->_parseWhere($cond));
	}
	protected function _delete($cond = [])
	{
		return $this->_dbInstance->delete($this->_table, $this->_parseWhere($cond));
	}
	protected function _query($sql, $params = [])
	{
		return $this->_dbInstance->query($sql, $params);
	}

	protected function _addJoin($table, $cond, $cols = null)
	{
		if (empty($cols))
			$cols = '*';
		if( $this->_joins )
        {
            //重复对一个表对join先屏蔽前个，，暂时解决
            foreach ( $this->_joins as $k=>$join )
            {
                if( $join['0'] == $table )
                {
                    unset($this->_joins[$k]);
                }
            }
        }
		$this->_joins[] = array($table, $cond, $cols);
	}

	protected function _addInnerJoin($table, $cond, $cols = null)
	{
		if (empty($cols))
			$cols = '*';
		$this->_inner_joins[] = array($table, $cond, $cols);
	}

	protected function _transactionQuery($sql, $params = [])
	{
		$this->_dbInstance->beginTransaction();
		try {
			$this->_query($sql, $params);
			$this->_dbInstance->commit();
		}
		catch (Exception $e)
		{
			$this->_dbInstance->rollBack();
			throw $e;
		}
	}

	protected function _fetchAll($sql, $params = [])
	{
		return $this->_dbInstance->fetchAll($sql, $params);
	}

	protected function _fetchAssoc($sql, $params = [])
	{
		return $this->_dbInstance->fetchAssoc($sql, $params);
	}

	protected function _fetchCol($sql, $params = [])
	{
		return $this->_dbInstance->fetchCol($sql, $params);
	}

	protected function _fetchOne($sql, $params = [])
	{
		return $this->_dbInstance->fetchOne($sql, $params);
	}

	protected function _fetchPairs($sql, $params = [])
	{
		return $this->_dbInstance->fetchPairs($sql, $params);
	}

	protected function _fetchRow($sql, $params = [])
	{
		return $this->_dbInstance->fetchRow($sql, $params);
	}
}


abstract class DAO extends baseControllerModel
{

}