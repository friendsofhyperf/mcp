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

use Hyperf\Context\RequestContext;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use ModelContextProtocol\SDK\Shared\Transport;

class SseServerTransport implements Transport
{
    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
    ) {
    }

    public function start(string $route): void
    {
        $fd = RequestContext::get()->getSwooleRequest()->fd; // @phpstan-ignore-line

        CoordinatorManager::until("mcp:fd:{$fd}")->yield();
    }

    public function handleMessage(string $message): void
    {
    }

    public function send(string $message): void
    {
    }

    public function close(): void
    {
    }

    public function setOnMessage(callable $callback): void
    {
    }

    public function setOnClose(callable $callback): void
    {
    }

    public function setOnError(callable $callback): void
    {
    }
}
