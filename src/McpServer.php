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

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpServer\Server;

class McpServer extends Server implements OnCloseInterface
{
    public function onClose($server, int $fd, int $reactorId): void
    {
        CoordinatorManager::until("mcp-sse:fd:{$fd}")->resume();
    }
}
