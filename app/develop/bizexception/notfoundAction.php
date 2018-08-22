<?php
require_once __DIR__.'/bizexception_common.php';

class develop_bizexception_notfound_Action extends develop_bizexception_common
{
    public $error;
	public function execute()
	{
		$this->__j7action['title'] = '404 Not Found';
	}
}