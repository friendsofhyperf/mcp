<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Transport;

use FriendsOfHyperf\MCP\SsePipeMessage;
use Swoole\Server;

class ProcessSseServerTransport extends CoroutineSseServerTransport
{
    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connectionManager->has($sessionId)) {
            $this->connectionManager->get($sessionId)->write("event: message\ndata: {$message}\n\n");
            return;
        }

        $server = $this->container->get(Server::class);
        $workerCount = $server->setting['worker_num'] - 1;

        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $server->worker_id) {
                continue;
            }

            $server->sendMessage(new SsePipeMessage($sessionId, $message), $workerId);
        }
    }
}
