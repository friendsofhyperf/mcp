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

class SessionIdGenerator implements \FriendsOfHyperf\MCP\Contract\SessionIdGenerator
{
    public function generate(): string
    {
        return $this->generateUuid();
    }

    /**
     * Generate a UUID v4.
     *
     * @return string the generated UUID
     */
    private function generateUuid(): string
    {
        // Generate 16 random bytes
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        // Format as string
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
