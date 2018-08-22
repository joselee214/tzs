<?php

//xunsearch的基础服务类

class xunSearch
{

    const libPath = '/usr/local/Cellar/xunsearch/sdk/php/lib/XS.php';
    public $xsInstance;

    public function __construct($app = 'demo')
    {
        file_exists(self::libPath) or die('lib file not exist!');
        require_once(self::libPath);
        $this->xsInstance = new XS($app);
    }


    //搜索part
    public function search($q)
    {
        $search = $this->xsInstance->search;
        $search->setCharset('UTF-8');
        //$search->setFuzzy();

        // set offset, limit
//        $p = max(1, intval($p));
//        $n = XSSearch::PAGE_SIZE;
//        $search->setLimit($n, ($p - 1) * $n);
        //$search->setLimit(5, 10); // 设置返回结果最多为 5 条，并跳过前 10 条

        $docs = $search->search($q);
        return $docs;
    }

    public function highlight($value)
    {
        return $this->xsInstance->search->highlight($value);
    }

    public function getLastCount()
    {
        return $this->xsInstance->search->getLastCount();
    }

    public function getDbTotal()
    {
        return $this->xsInstance->search->getDbTotal();
    }


    //索引part
    public function addIndex()
    {

    }


    public function delIndex()
    {

    }

    public function updIndex()
    {

    }

}


?>