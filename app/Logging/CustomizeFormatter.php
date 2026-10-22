<?php
namespace App\Logging;

use Monolog\Formatter\JsonFormatter;

class CustomizeFormatter
{
    /**
     * Aplica JSON a todos los handlers del canal.
     *
     * @param \Monolog\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter(
                JsonFormatter::BATCH_MODE_JSON,
                true
            ));
        }
    }
}
