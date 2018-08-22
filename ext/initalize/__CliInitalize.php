<?php
class CliInitalize extends J7Initalize
{
    protected function _initRouter()
    {
        $this->_router = new cliRouter($this->_url_path);
    }
}
