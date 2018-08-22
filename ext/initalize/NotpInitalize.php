<?php
class NotpInitalize extends CommonInitalize
{
    protected function _setRewriteBaseUrlPath($url_prefix_path='')
    {
        $url_prefix_path_tmp = $this->_url_path->getRewriteBase();
        $url_prefix_path = $url_prefix_path_tmp.'/notp';
        $this->_url_path->setRewriteBase( $url_prefix_path);
//        $this->_url_path->setActionRewriteBase($url_prefix_path_tmp); //会影响picspace，根据 /notp 来判断的

        return $this->_url_path;
    }

    protected function _initResultType()
    {
        $this->_disposal->setDefaultResuleType([null,['view'=>'NoTemplateView']]); // 0 强制返回模式 替换 引用返回模式
    }
}
