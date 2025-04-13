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

use Hyperf\Context\Context;
use Hyperf\Engine\Http\EventStream;

class RequestContext
{
    public static function setSessionId(string $sessionId): string
    {
        return Context::set('mcp.sse.sessionId', $sessionId);
    }

    public static function getSessionId(): ?string
    {
        return Context::get('mcp.sse.sessionId');
    }

    public static function setConnection(EventStream $connection): EventStream
    {
        return Context::set('mcp.sse.connection', $connection);
    }

    public static function getConnection(): ?EventStream
    {
        return Context::get('mcp.sse.connection');
    }
}
