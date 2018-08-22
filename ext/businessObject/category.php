<?php
class category
{

    // cate tree 追加path路径
    public static function addCateTreePath($array, $parentpath = '')
    {
        foreach ($array as $k => $tree) {
            $array[$k]['path'] = $parentpath ? substr(str_replace('_', '', $parentpath), 0, -1) : $parentpath;
            if (isset($tree['childrens'])) {
                //2::建材馆@146::门窗 //2::建材馆
                $pattern = $parentpath . '_' . $tree['id'] . '::' . $tree['name'] . '@';
                $array[$k]['childrens'] = self::addCateTreePath($tree['childrens'], $pattern);
            }
        }
        return $array;
    }

    //to create html dropdown list
    public static function structTree($modules = [], $editid = null, $layer = null, $space = null, $output = null,$child = 'childmodules',$title='name')
    {
        $space .= "&nbsp;&nbsp;";
        if (count($modules) > 0) {
            foreach ($modules as $key => $module) {
                if (isset($module[$child]) && count($module[$child]) > 0) {
                    if ($module['id'] == $editid) {
                        $output .= "<OPTION value = " . $module['id'] . " selected>" . $space . "|+" . $module[$title] . "</OPTION>\n";
                    } else {
                        $output .= "<OPTION value = " . $module['id'] . ">" . $space . "|+" . $module[$title] . "</OPTION>\n";
                    }

                } else {
                    if ($module['id'] == $editid) {
                        $output .= "<OPTION value = " . $module['id'] . " selected>" . $space . "|-&nbsp;" . $module[$title] . "</OPTION>\n";
                    } else {
                        $output .= "<OPTION value = " . $module['id'] . ">" . $space . "|-&nbsp;" . $module[$title] . "</OPTION>\n";
                    }
                }
                $layer++;
                debug($child,'child');
                $output .= self::structTree($module[$child] = isset($module[$child]) ? $module[$child] : [], $editid, $layer, $space, null , $child, $title);
                $layer--;
            }
        }
        return $output;
    }

    //厂商分类的一个小调整
    public static function structTreenew($modules = [],
                                         $editid = null,
                                         $layer = null,
                                         $space = null,
                                         $output = null,
                                         $child = 'childmodules',
                                         $title='title',
                                         $disabled = false,
                                         $notSelectable = [],
                                         $forceDisable = false,
                                         $maxdeep = 0
    )
    {
        $space .= "&nbsp;&nbsp;";
        if (count($modules) > 0) {
            foreach ($modules as $key => $module) {
                $_disabled = '';
                $tmpFlag = false;

                if ( in_array($module['fcid'], $notSelectable) || in_array($module['pid'], $notSelectable) || $forceDisable) {
                    $_disabled = ' disabled="disabled"';
                    $tmpFlag = true;
                }

                $_selected = $module['fcid'] == $editid ? ' selected':'';
                if (isset($module[$child]) && count($module[$child]) > 0) {
                    if (!$_disabled) {
                        $_disabled = $disabled ? ' disabled="disabled"' :'';
                    }
                    $split = "|+";

                } else {
                    $split = "|-&nbsp;";
                }

                if( empty($maxdeep) || $layer<$maxdeep ){
                    $output .= "<OPTION value = ".$module['fcid'] . "{$_disabled} ".$_selected.">" . $space . $split . $module[$title] . "</OPTION>\n";

                    $layer++;
                    $output .= self::structTreenew($module[$child] = isset($module[$child]) ? $module[$child] : [], $editid, $layer, $space, null, $child, $title, $disabled, $notSelectable ,$tmpFlag,$maxdeep);
                    $layer--;
                }
            }
        }
        return $output;
    }

}

?>