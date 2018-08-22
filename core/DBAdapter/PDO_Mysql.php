<?php
/**
 * From Zend Framework
 */ 


require_once __DIR__.'/PDO_Abstract.php';

class PDO_Mysql extends Adapter_Pdo_Abstract
{
    protected $_pdoType = 'mysql';
    public function quoteIdentifier($ident)
    {
        $ident = str_replace('`', '``', $ident);
        return "`$ident`";
    }
    public function listTables()
    {
        return $this->fetchCol('SHOW TABLES');
    }
    public function describeTable($table)
    {
        $sql = "DESCRIBE $table";
        $result = $this->fetchAll($sql);
        $descr = [];
        foreach ($result as $key => $val) {
            $descr[$val['field']] = array(
                'name'    => $val['field'],
                'type'    => $val['type'],
                'notnull' => (bool) ($val['null'] != 'YES'), // not null is NO or empty, null is YES
                'default' => $val['default'],
                'primary' => (strtolower($val['key']) == 'pri'),
            );
        }
        return $descr;
    }
     public function limit($sql, $count, $offset)
     {
        if ($count > 0) {
            $offset = ($offset > 0) ? $offset : 0;
            $sql .= "LIMIT $offset, $count";
        }
        return $sql;
    }
}
