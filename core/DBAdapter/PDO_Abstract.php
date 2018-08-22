<?php
/**
 * Zend Framework
 *
 */


require_once __DIR__.'/Adapter_Abstract.php';


abstract class Adapter_Pdo_Abstract extends Adapter_Abstract
{
    /**
     * Creates a PDO DSN for the adapter from $this->_config settings.
     *
     * @return string
     */
    protected function _dsn()
    {
        // baseline of DSN parts
        $dsn = $this->_config;

        // don't pass the username and password in the DSN
        unset($dsn['username']);
        unset($dsn['password']);

        // use all remaining parts in the DSN
        foreach ($dsn as $key => $val) {
            $dsn[$key] = "$key=$val";
        }

        return $this->_pdoType . ':' . implode(';', $dsn);
    }


    /**
     * Creates a PDO object and connects to the database.
     *
     * @return void
     */
    protected function _connect()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_connection) {
            //$this->_connection=null;
			return;
        }
		//var_dump($this->_dsn(),$this->_config);
        // check for PDO extension
        if (!extension_loaded('pdo')) {
            throw new J7Exception('The PDO extension is required for this adapter but not loaded');
        }

        // check the PDO driver is available
        if (!in_array($this->_pdoType, PDO::getAvailableDrivers())) {
            throw new J7Exception('The ' . $this->_pdoType . ' driver is not currently installed');
        }

        // create PDO connection
        $q = $this->_profiler->queryStart('connect', Db_Profiler::CONNECT);

        try {

            if( isset($this->_config['charset']) && $this->_config['charset']!='utf8mb4' )
                $alertcommd = "set NAMES '".$this->_config['charset']."'";
            else
                $alertcommd ="set NAMES 'utf8mb4'";

            $option = array(PDO::ATTR_AUTOCOMMIT=>1, PDO::ATTR_CASE=>PDO::CASE_NATURAL,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND=>$alertcommd);

                $this->_connection = new PDO(
                    $this->_dsn(),
                    $this->_config['username'],
                    $this->_config['password'],
                    $option
                );
            $this->_profiler->queryEnd($q);

            //var_dump( $this->_connection->getAttribute(0) );


//            // force names to lower case
//            $this->_connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
//
//             //always use exceptions.
//            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            /** @todo Are there other portability attribs to consider? */
        } catch (PDOException $e) {
            throw new J7Exception($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            throw new J7Exception( $e->getMessage() );
        }

    }


    /**
     * Prepares an SQL statement.
     *
     * @param string $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        $this->_connect();

//      echo '+++<font color="red">';
//        echo $sql;
//       echo '</font><br>';
        return $this->_connection->prepare($sql);
    }


    /**
     * Gets the last inserted ID.
     *
     * @param  string $tableName   table or sequence name needed for some PDO drivers
     * @param  string $primaryKey  primary key in $tableName need for some PDO drivers
     * @return integer
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $this->_connect();
        return $this->_connection->lastInsertId();
    }
    /**
     * Begin a transaction.
     */
    protected function _beginTransaction()
    {
        $this->_connection->beginTransaction();
    }


    /**
     * Commit a transaction.
     */
    protected function _commit()
    {
        $this->_connection->commit();
    }


    /**
     * Roll-back a transaction.
     */
    protected function _rollBack() {
        $this->_connection->rollBack();
    }


    /**
     * Quote a raw string.
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        return $this->_connection->quote($value);
    }


    /**
     * Set the PDO fetch mode.
     *
     * @param int $mode A PDO fetch mode.
     * @return void
     * @todo Support FETCH_CLASS and FETCH_INTO.
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case PDO::FETCH_LAZY:
            case PDO::FETCH_ASSOC:
            case PDO::FETCH_NUM:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_NAMED:
            case PDO::FETCH_OBJ:
                $this->_fetchMode = $mode;
                break;
            default:
                throw new J7Exception('Invalid fetch mode specified');
                break;
        }
    }

}