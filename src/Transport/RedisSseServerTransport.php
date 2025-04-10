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

use FriendsOfHyperf\MCP\Contract\IdGenerator;
use FriendsOfHyperf\MCP\Contract\SessionIdGenerator;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\co;
use function Hyperf\Support\msleep;

class RedisSseServerTransport extends CoroutineSseServerTransport
{
    public function __construct(
        protected ContainerInterface $container,
        protected Redis $redis,
        protected string $prefix = '',
        string $endpoint = '/sse',
    ) {
        parent::__construct(
            $container->get(RequestInterface::class),
            $container->get(ResponseInterface::class),
            $container->get(IdGenerator::class),
            $container->get(SessionIdGenerator::class),
            $endpoint
        );
    }

    public function start(): void
    {
        co(function () {
            while (true) { // @phpstan-ignore-line
                $this->redis->ping();
                msleep(1000);
            }
        });
        co(function () {
            $this->redis->psubscribe(["{$this->prefix}mcp.sse.*"], function ($redis, $pattern, $channel, $message) {
                $sessionId = (string) substr($channel, strlen("{$this->prefix}mcp.sse."));
                if (isset($this->connections[$sessionId])) {
                    $this->connections[$sessionId]->write("event: message\ndata: {$message}\n\n");
                }
            });
        });

        parent::start();
    }

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if (! isset($this->connections[$sessionId])) {
            return;
        }

        $this->redis->publish("{$this->prefix}mcp.sse.{$sessionId}", $message);
    }
}
