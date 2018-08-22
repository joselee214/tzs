<?php
class arrayHelp
{

    /**
     * 将一个平面的二维数组按照指定的字段转换为树状结构
     *
     * 当 $returnReferences 参数为 true 时，返回结果的 tree 字段为树，refs 字段则为节点引用。
     * 利用返回的节点引用，可以很方便的获取包含以任意节点为根的子树。
     *
     * @param array $arr 原始数据
     * @param string $fid 节点ID字段名
     * @param string $fparent 节点父ID字段名
     * @param string $fchildrens 保存子节点的字段名
     * @param boolean $returnReferences 是否在返回结果中包含节点引用
     *
     * return array
     */
    function array_to_tree(&$arr, $fid, $fparent = 'parent_id',$fchildrens = 'childrens', $returnReferences = false)
    {
        $pkvRefs = array();
        foreach ($arr as $offset => $row) {
            $pkvRefs[$row[$fid]] =& $arr[$offset];
        }

        $tree = array();
        foreach ($arr as $offset => $row) {
            $parentId = $row[$fparent];
            if ($parentId) {
                if (!isset($pkvRefs[$parentId])) {
                    continue;
                }
                $parent =& $pkvRefs[$parentId];
                $parent[$fchildrens][] =& $arr[$offset];
            } else {
                $tree[] =& $arr[$offset];
            }
        }
        if ($returnReferences) {
            return array('tree' => $tree, 'refs' => $pkvRefs);
        } else {
            return $tree;
        }
    }



}

?>