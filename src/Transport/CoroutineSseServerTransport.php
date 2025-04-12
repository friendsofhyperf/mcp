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

class CoroutineSseServerTransport extends SseServerTransport
{
    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connections->has($sessionId)) {
            $this->connections->get($sessionId)->write("event: message\ndata: {$message}\n\n");
        }
    }
}
