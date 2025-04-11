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

use Hyperf\Engine\Http\EventStream;

class ConnectionManager
{
    /**
     * @var array<string, EventStream>
     */
    protected array $connections = [];

    public function register(string $sessionId, EventStream $connection): void
    {
        $this->connections[$sessionId] = $connection;
    }

    public function unregister(string $sessionId): void
    {
        unset($this->connections[$sessionId]);
    }

    public function has(string $sessionId): bool
    {
        return isset($this->connections[$sessionId]);
    }

    public function get(string $sessionId): ?EventStream
    {
        return $this->connections[$sessionId] ?? null;
    }
}
