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

use FriendsOfHyperf\MCP\Contract\SseServerTransport;
use FriendsOfHyperf\MCP\Transport\SseCoroutineServerTransport;

define('BASE_PATH', dirname(__DIR__));

return [
    'dependencies' => [
        SseServerTransport::class => SseCoroutineServerTransport::class,
    ],
    'publish' => [
        [
            'id' => 'config',
            'description' => 'The configuration file of mcp.',
            'source' => __DIR__ . '/../publish/mcp.php',
            'destination' => BASE_PATH . '/config/autoload/mcp.php',
        ],
    ],
];
