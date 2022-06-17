<?php

namespace MediciVN\Core\Logger;

use MediciVN\Core\Exceptions\MediciException;
use Exception;
use Illuminate\Support\Facades\Log;

class FileLogger implements LoggerInterface
{
    /**
     * Create a path log & log info to log file.
     *
     * @param string $action to create path file
     * @param string $description short name what you want to log
     * @param array $data data to log
     * @param array $params to create a path file
     *                            * [] => 'logs/action/Ymd/userLogin/action.log'
     *                            * ['log_user' => false] => 'logs/action/Ymd/action.log'
     *                            * ['log_user' => false, 'user_id' => 123] => 'logs/action/Ymd/123/action.log'
     */
    public function log(string $action, string $description, array $data = [], array $params = [])
    {
        try {
            $path = storage_path('logs/' . $action . '/' . date('Y-m-d') . '/' . $action . '.log');

            if (\array_key_exists('user_id', $params)) {
                $path = storage_path('logs/' . $action . '/' . date('Y-m-d') . '/' . $params['user_id'] . '/' . $action . '.log');
            }
            if (auth()->user() && (!\array_key_exists('log_user', $params) || $params['log_user'])) {
                $params['user_id'] = auth()->user()->id;
                $path = storage_path('logs/' . $action . '/' . date('Y-m-d') . '/' . $params['user_id'] . '/' . $action . '.log');
            }

            $level = 'info';
            if (\array_key_exists('level', $params)) {
                $level = $params['level'];
            }
            $log = [
                'driver' => 'single',
                'path' => $path,
                'level' => $level,
                'days' => 14,
            ];
            config(['logging.channels.' . $action => $log]);
            Log::channel($action)->info([
                'Description' => trim($description),
                'Data' => $data,
            ]);
        } catch (Exception $e) {
            throw new MediciException($e->getCode(), $e->getMessage());
        }
    }
}
