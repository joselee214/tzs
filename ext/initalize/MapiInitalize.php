<?php
class MapiInitalize extends CommonInitalize
{

    protected function _setRewriteBaseUrlPath($url_prefix_path='')
    {
        $url_prefix_path_tmp = $this->_url_path->getRewriteBase();
        $url_prefix_path = $url_prefix_path_tmp.'/mapi';
        $this->_url_path->setRewriteBase( $url_prefix_path);

        return $this->_url_path;
    }

    protected function _initResultType()
    {
        $this->_disposal->setDefaultResuleType([null,['view'=>'MapiView','action'=>'MapiView']]);
    }

}