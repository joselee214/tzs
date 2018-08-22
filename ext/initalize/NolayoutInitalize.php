<?php
class Nolayoutnitalize extends CommonInitalize
{
    protected function _setRewriteBaseUrlPath($url_prefix_path='')
    {
        $url_prefix_path = $this->_url_path->getRewriteBase().'/nolayout';
        $this->_url_path->setRewriteBase( $url_prefix_path);
        return $this->_url_path;
    }
}
