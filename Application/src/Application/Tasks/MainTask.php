<?php

namespace Application\Tasks;

class MainTask extends \Phalcon\Cli\Task
{

    public function mainAction()
    {
        echo "TEST!!! Application default console task" . PHP_EOL;
    }

}