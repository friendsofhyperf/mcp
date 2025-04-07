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

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use ModelContextProtocol\SDK\Server\McpServer;
use RuntimeException;

class ServerManager
{
    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getServer(string $name): McpServer
    {
        $servers = $this->config->get('mcp.servers', []);

        if (! isset($servers[$name])) {
            throw new RuntimeException(sprintf('Server %s not found.', $name));
        }

        $serverInfo = Arr::only($servers[$name] ?? [], ['name', 'version', 'description']);

        return new McpServer($serverInfo);
    }
}
