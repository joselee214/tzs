<?php
class tzs_cli_deleteupload_Action extends tzs_cli_common
{

    public function execute()
    {
        echo Util::dateTime().PHP_EOL;

        $this->jobService->deleteNouseFiles();

        echo Util::dateTime().PHP_EOL;
    }
}