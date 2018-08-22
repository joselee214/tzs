<?php
class tzs_cli_once_Action extends tzs_cli_common
{

    public function execute()
    {
        echo Util::dateTime().PHP_EOL;

        $this->jobService->getOnceViewLogs();

        echo Util::dateTime().PHP_EOL;
    }
}