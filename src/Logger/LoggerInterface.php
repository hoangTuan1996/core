<?php

namespace Core\Logger;

interface LoggerInterface
{
    /**
     * Create a path log & log info to log file
     *
     * @param string $action to create path file
     * @param string $description short name what you want to log
     * @param array $data data to log
     * @param array $params to create a path file
     * * [] => 'logs/action/Ymd/userLogin/action.log'
     * * ['log_user' => false] => 'logs/action/Ymd/action.log'
     * * ['log_user' => false, 'user_id' => 123] => 'logs/action/Ymd/123/action.log'
     *
     * @return void
     */
    public function log(string $action, string $description, array $data = [], array $params = []);
}
