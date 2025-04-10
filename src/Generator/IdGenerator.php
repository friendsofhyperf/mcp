<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Generator;

class IdGenerator implements \FriendsOfHyperf\MCP\Contract\IdGenerator
{
    public function __construct(
        protected ?int $lastId = null,
    ) {
    }

    public function generate(): int
    {
        if ($this->lastId === null) {
            $this->lastId = time();
        }

        return ++$this->lastId;
    }
}
