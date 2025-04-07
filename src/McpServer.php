<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP;

use FriendsOfHyperf\MCP\Exception\Handler\McpSseExceptionHandler;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpServer\Server;

class McpServer extends Server implements OnCloseInterface
{
    protected string $version = '1.0.0';

    public function onClose($server, int $fd, int $reactorId): void
    {
        CoordinatorManager::until("mcp:fd:{$fd}")->resume();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            McpSseExceptionHandler::class,
        ];
    }
}
