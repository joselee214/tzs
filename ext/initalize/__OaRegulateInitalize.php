<?php
class OaRegulateInitalize extends J7Initalize
{
    protected function _initFilters()
    {
        //Filters加载
        $this->_filters[] = new OaFilter();
    }
}