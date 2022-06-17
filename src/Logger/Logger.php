<?php

namespace MediciVN\Core\Logger;

class Logger
{
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $action, string $description, array $data = [], array $params = [])
    {
        return $this->logger->log($action, $description, $data, $params);
    }
}
