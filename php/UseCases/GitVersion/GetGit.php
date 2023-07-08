<?php

namespace TeamPortal\UseCases;


class GetGit implements Interactor
{

    public function __construct()
    {

    }
    

    public function Execute(object $data = null)
    {
        if ($this->isShellExecEnabled('shell_exec')) {
            shell_exec('echo "hello world"');
        }
    
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        $hash = trim(shell_exec('git rev-parse HEAD'));

        header('Content-Type: application/json');
        return json_encode(array('branch' => $branch, 'hash' => $hash));
    }

    private function isShellExecEnabled($func) {
        return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
    }

    
}
