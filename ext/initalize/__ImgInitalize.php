<?php
class ImgInitalize extends J7Initalize
{

    public function dispatch()
    {
//        header('Content-Type:image/jpg');
        parent::dispatch();
    }

    protected function _initRouter()
    {
        $this->_router = new imgRouter($this->_url_path);
    }
}