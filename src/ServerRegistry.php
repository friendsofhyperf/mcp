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

use Hyperf\Contract\ConfigInterface;
use ModelContextProtocol\SDK\Server\McpServer;
use RuntimeException;

class ServerRegistry
{
    /**
     * @var array<string, McpServer>
     */
    protected array $servers = [];

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function register(string $name, McpServer $server): void
    {
        $this->servers[$name] = $server;
    }

    public function get(string $name): McpServer
    {
        if (! isset($this->servers[$name])) {
            throw new RuntimeException(sprintf('Server %s not found.', $name));
        }

        return $this->servers[$name];
    }
}
