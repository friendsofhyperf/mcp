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

use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\co;
use function Hyperf\Support\msleep;

class RedisSseServerTransport extends CoroutineSseServerTransport
{
    protected bool $isSubscribing = false;

    public function __construct(
        ContainerInterface $container,
        string $endpoint = '/sse',
        protected ?Redis $redis = null,
        protected string $prefix = '',
    ) {
        $this->redis ??= $container->get(Redis::class);
        parent::__construct($container, $endpoint);
    }

    public function start(): void
    {
        if (! $this->isSubscribing) {
            co(function () {
                while (true) { // @phpstan-ignore-line
                    $this->redis->ping();
                    msleep(1000);
                }
            });
            co(function () {
                $this->redis->psubscribe(["{$this->prefix}mcp.sse.*"], function ($redis, $pattern, $channel, $message) {
                    $sessionId = (string) substr($channel, strlen("{$this->prefix}mcp.sse."));
                    $this->connectionManager->get($sessionId)?->write("event: message\ndata: {$message}\n\n");
                });
            });

            $this->isSubscribing = true;
        }

        parent::start();
    }

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connectionManager->has($sessionId)) {
            $this->connectionManager->get($sessionId)->write("event: message\ndata: {$message}\n\n");
            return;
        }

        $this->redis->publish("{$this->prefix}mcp.sse.{$sessionId}", $message);
    }
}
