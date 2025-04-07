<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */
return [
    'demo' => [
        'name' => 'demo',
        'description' => 'This is a demo route',
        // The route of the sse server
        'sse' => [
            'server' => 'http',
            'route' => '/sse',
        ],
        // Bind the name of the command
        'stdio' => [
            'command' => 'mcp:demo',
        ],
    ],
];
