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
    protected ?Server $server;

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connections->has($sessionId)) {
            $this->connections->get($sessionId)->write("event: message\ndata: {$message}\n\n");
            return;
        }

        $this->server ??= $this->container->get(Server::class);
        $workerCount = $this->server->setting['worker_num'] - 1;
        $pipeMessage = new SsePipeMessage($sessionId, $message);

        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $this->server->worker_id) {
                continue;
            }

            $this->server->sendMessage($pipeMessage, $workerId);
        }
    }
}
