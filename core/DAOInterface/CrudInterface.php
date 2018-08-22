<?php
/**
 * 此接口用于需要 CRUD 基本操作的那些 DAO，如果某个 DAO 的接口继承了此接口，
 * 则相对应的 DAO 实现也同样需要继承 XxCrudDAO 的实现。
 * 例如：
 * <code>interface MemberDAO extends CrudDAO</code>
 * 那么对应的数据库实现需要继承自 DbCrudDAO：
 * <code>class MemberDbDAO extends DbCrudDAO</code>
 */
interface CrudInterface
{

    public function __construct();      //一定是无参数的

    public function __getPkName();

//    public function changeDb($dbindex=0);
//
//    public function changeTable($table);

    /**
     * 通过主键获取数据。
     *
     * @param   mixed           $pk     主键信息。
     * @param   string|array    $datas  额外辅助。
     * @return  array
     */
    public function getByPk($pk, $pinfo = []);
    
    /**
     * 插入新的数据记录。
     *
     * @param   array   $datas  数据数组。
     * @return  mixed   返回新插入记录的主键值，通常为最后的 ID 号。
     */
    public function add($datas);

    /**
     * 根据给定的主键值（在参数 $datas 中）更新对应的记录数据。
     *
     * @param   array   $datas  数据数组。
     * @return  boolean
     */
    public function updateByPk($datas, $pinfo = []);

    /**
     * 根据给定的主键值或由主键值组成的数组，删除相应的记录。
     *
     * @param   mixed   $ids    主键值或主键值数组。
     * @return  boolean
     */
    public function deleteByPk($ids, $pinfo = []);



    /**
     * 获取数据集。
     *
     * @param   string|array    $cond   条件数组，如：array('id' => 12)。
     * @param   string          $order  排序方式，如：array('id' => 'DESC')。
     * @param   integer         $count  获取的数据记录数。
     * @param   integer         $offset 记录开始位置。
     * @param   string|array    $cols   选择的字段。
     * @return  array
     */
    function get($cond = null, $order = null, $count = null, $offset = null, $cols = null);

    /**
     * 获取符合条件的记录总数。
     *
     * @param   array   $cond   条件数组，如：array('id' => 12)。
     * @return  integer
     */
    public function getCount($cond = []);

    /**
     * 更新记录数据。
     *
     * @param   array   $datas  数据数组。
     * @return  boolean
     */
    public function update($datas, $cond = [], $pinfo = []);




    /**
     * 删除符合条件的记录。
     *
     * @param   array   $cond   条件数组，如：array('id' => 12)。
     * @return  boolean
     */
    public function delete($cond = [], $pinfo = []);




//	public function deleteByFk($fk, $value);
//	public function query($sql);
}