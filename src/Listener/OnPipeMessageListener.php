<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Listener;

use FriendsOfHyperf\MCP\ConnectionManager;
use FriendsOfHyperf\MCP\SsePipeMessage;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(
        protected ConnectionManager $connections,
    ) {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * @param OnPipeMessage|object $event
     */
    public function process(object $event): void
    {
        $message = $event->data;

        if ($event instanceof OnPipeMessage && $message instanceof SsePipeMessage) {
            $this->connections->get($message->sessionId)?->write("event: message\ndata: {$message->message}\n\n");
        }
    }
}
