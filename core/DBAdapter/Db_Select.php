<?php
/**
 * Zend Framework
 */
class Db_Select {

    protected $_adapter;

    /**
     * The component parts of a SELECT statement.
     *
     * @var array
     */
    protected $_parts = array(
        'distinct'    => false,
        'forUpdate'   => false,
        'cols'        => [],
        'from'        => [],
        'join'        => [],
        'where'       => [],
        'group'       => [],
        'having'      => [],
        'order'       => [],
        'limitCount'  => null,
        'limitOffset' => null,
    );

    /**
     * Tracks which columns are being select from each table and join.
     *
     * @var array
     */
    protected $_tableCols = [];


    /**
     * Class constructor
     *
     */
    public function __construct(Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
    }

    public function update_parts($key,$value)
    {
    	$this->_parts[$key]=$value;
    }

    public function get_parts($key)
    {
        return isset($this->_parts[$key])?$this->_parts[$key]:false;
    }

    /**
     * Converts this object to an SQL SELECT string.
     * @return string
     */
    public function __toString()
    {
        // initial SELECT [DISTINCT] [FOR UPDATE]
        $sql = "SELECT";
        if ($this->_parts['distinct']) {
            $sql .= " DISTINCT";
        }
        if ($this->_parts['forUpdate']) {
            $sql .= " FOR UPDATE";
        }
        $sql .= "\n\t";

        // add columns
        if ($this->_parts['cols']) {
            $sql .= implode(",\n\t", $this->_parts['cols']) . "\n";
        }

        // from these tables
        if ($this->_parts['from']) {
            $sql .= "FROM ";
            $sql .= implode(", ", $this->_parts['from']) . "\n";
        }

        // joined to these tables
        if ($this->_parts['join']) {
            $list = [];
            foreach ($this->_parts['join'] as $join) {
                $tmp = '';
                // add the type (LEFT, INNER, etc)
                if (! empty($join['type'])) {
                    $tmp .= strtoupper($join['type']) . ' ';
                }
                // add the table name and condition
                $tmp .= 'JOIN ' . $join['name'];
                $tmp .= ' ON ' . $join['cond'];
                // add to the list
                $list[] = $tmp;
            }
            // add the list of all joins
            $sql .= implode("\n", $list) . "\n";
        }

        // with these where conditions
        if ($this->_parts['where']) {
            $sql .= "WHERE\n\t";
            $sql .= implode("\n\t", $this->_parts['where']) . "\n";
        }

        // grouped by these columns
        if ($this->_parts['group']) {
            $sql .= "GROUP BY\n\t";
            $sql .= implode(",\n\t", $this->_parts['group']) . "\n";
        }

        // having these conditions
        if ($this->_parts['having']) {
            $sql .= "HAVING\n\t";
            $sql .= implode("\n\t", $this->_parts['having']) . "\n";
        }

        // ordered by these columns
        if ($this->_parts['order']) {
            $sql .= "ORDER BY\n\t";
            $sql .= implode(",\n\t", $this->_parts['order']) . "\n";
        }

        // determine count
        $count = ! empty($this->_parts['limitCount'])
            ? (int) $this->_parts['limitCount']
            : 0;

        // determine offset
        $offset = ! empty($this->_parts['limitOffset'])
            ? (int) $this->_parts['limitOffset']
            : 0;

        // add limits, and done
        return trim($this->_adapter->limit($sql, $count, $offset));
    }



    public function fetchAll($classname=null,$dao=null)
    {
        $result = $this->_adapter->query($this->__toString());
        if($classname && $this->get_parts('cols')!=['count(*)'] )
            return $result->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,$classname,array($dao,false));
        else
            return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**
     * Makes the query SELECT DISTINCT.
     */
    public function distinct($flag = true)
    {
        $this->_parts['distinct'] = (bool) $flag;
        return $this;
    }


    /**
     * Makes the query SELECT FOR UPDATE.
     */
    public function forUpdate($flag = true)
    {
        $this->_parts['forUpdate'] = (bool) $flag;
        return $this;
    }


    /**
     */
    public function from($name, $cols = '*')
    {
        // add the table to the 'from' list
        $this->_parts['from'] = array_merge(
            $this->_parts['from'],
            (array) $name
        );

        // add to the columns from this table
        $this->_tableCols($name, $cols);
        return $this;
    }

    /**
     * Populate the {@link $_parts} 'join' key
     *
     * Does the dirty work of populating the join key.
     */
    protected function _join($type, $name, $cond, $cols=null)
    {
        if (!in_array($type, array('left', 'inner'))) {
            $type = null;
        }

        $this->_parts['join'][] = array(
            'type' => $type,
            'name' => $name,
            'cond' => $cond,
            'cols' => $cols
        );

        // add to the columns from this joined table
        $this->_tableCols($name, $cols);
        return $this;
    }

    /**
     * Adds a JOIN table and columns to the query.
     *
     */
    public function join($name, $cond, $cols = null)
    {
        return $this->_join(null, $name, $cond, $cols);
    }


    /**
     * Add a LEFT JOIN table and colums to the query
     */
    public function joinLeft($name, $cond, $cols = null) 
    {
        return $this->_join('left', $name, $cond, $cols);
    }

    /**
     * Add an INNER JOIN table and colums to the query
     *
     */
    public function joinInner($name, $cond, $cols = null) 
    {
        return $this->_join('inner', $name, $cond, $cols);
    }


    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     * 
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     * 
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string $cond The WHERE condition.
     * @param string $val A single value to quote into the condition.
     * @return void
     */
    public function where($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_adapter->quoteInto($cond, $val);
        }

        if ($this->_parts['where']) {
            $this->_parts['where'][] = "AND ($cond)";
        } else {
            $this->_parts['where'][] = "($cond)";
        }
        return $this;
    }


    /**
     * Adds a WHERE condition to the query by OR.
     *
     * Otherwise identical to where().
     *
     * @param string $cond The WHERE condition.
     * @param string $val A value to quote into the condition.
     * @return void
     *
     * @see where()
     */
    public function orWhere($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_adapter->quoteInto($cond, $val);
        }

        if ($this->_parts['where']) {
            $this->_parts['where'][] = "OR ($cond)";
        } else {
            $this->_parts['where'][] = "($cond)";
        }

        return $this;
    }


    /**
     * Adds grouping to the query.
     *
     * @param string|array $spec The column(s) to group by.
     * @return void
     */
    public function group($spec)
    {
        if (is_string($spec)) {
            $spec = explode(',', $spec);
        } else {
            settype($spec, 'array');
        }

        foreach ($spec as $val) {
            $this->_parts['group'][] = trim($val);
        }

        return $this;
    }


    /**
     * Adds a HAVING condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. See {@link where()} for an example
     *
     * @param string $cond The HAVING condition.
     * @param string $val A single value to quote into the condition.
     * @return void
     */
    public function having($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_adapter->quoteInto($cond, $val);
        }

        if ($this->_parts['having']) {
            $this->_parts['having'][] = "AND $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }

        return $this;
    }


    /**
     * Adds a HAVING condition to the query by OR.
     *
     * Otherwise identical to orHaving().
     *
     * @param string $cond The HAVING condition.
     * @param string $val A single value to quote into the condition.
     * @return void
     *
     * @see having()
     */
    public function orHaving($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_adapter->quoteInto($cond, $val);
        }

        if ($this->_parts['having']) {
            $this->_parts['having'][] = "OR $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }

        return $this;
    }


    /**
     * Adds a row order to the query.
     *
     * @param string|array $spec The column(s) and direction to order by.
     * @return void
     */
    public function order($spec)
    {
        if (is_string($spec)) {
        	if (substr($spec,0,1)!='(')
            	$spec = explode(',', $spec);
            else
            	$spec=[$spec];
        }
        if(!is_array($spec)) {
            throw new coreException('only array supported in order by condition!');
        }

        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $key => $val) {
            if( is_numeric($key) )
            {
                debug('must change string to array in order by condition!');
                $val = trim($val);
                $asc  = (strtoupper(substr($val, -4)) == ' ASC');
                $desc = (strtoupper(substr($val, -5)) == ' DESC');
                if (! $asc && ! $desc) {
                    $val .= ' ASC';
                }
                $this->_parts['order'][] = trim($val);
            }
            elseif( in_array(strtoupper(trim($val)),['ASC','DESC']) )
            {
                $this->_parts['order'][] = trim($key).' '.trim($val);
            }
            else
            {
                throw new coreException('only ASC / DESC supported in order by condition!');
            }
        }
        return $this;
    }


    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count The number of rows to return.
     * @param int $offset Start returning after this many rows.
     * @return void
     */
    public function limit($count = null, $offset = null)
    {
        $this->_parts['limitCount']  = (int) $count;
        $this->_parts['limitOffset'] = (int) $offset;
        return $this;
    }

    protected function _tableCols($tbl, $cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        }
        if(!is_array($cols)) {
            throw new coreException('only array supported in columns !');
        }
        foreach ($cols as $col) {
            $col = trim($col);
            if( strpos($col,'.')===false && strpos($col,' ')===false )
            {
                $col = $tbl.'.'.$col;
            }
            $this->_parts['cols'][] = trim($col);
        }
        return $this;
    }

}
