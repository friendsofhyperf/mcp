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

use FriendsOfHyperf\MCP\Contract\ServerTransport;
use ModelContextProtocol\SDK\Server\Transport\Traits\InteractsWithCallbacks;

abstract class AbstractTransport implements ServerTransport
{
    use InteractsWithCallbacks;

    abstract public function writeMessage(string $message): void;

    abstract public function close(): void;
}
